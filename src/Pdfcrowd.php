<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient;

use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;
use Swis\PdfcrowdClient\Http\FactoryInterface;
use Swis\PdfcrowdClient\Http\RequestFactory;
use Swis\PdfcrowdClient\Http\RequestInterface;

class Pdfcrowd
{
    const CLIENT_VERSION = '0.1';
    const API_PREFIX = 'https://pdfcrowd.com/api';

    const PAGE_LAYOUT_SINGLE_PAGE = 1;
    const PAGE_LAYOUT_CONTINUOUS = 2;
    const PAGE_LAYOUT_CONTINUOUS_FACING = 3;

    const PAGE_MODE_NONE_VISIBLE = 1;
    const PAGE_MODE_THUMBNAILS_VISIBLE = 2;
    const PAGE_MODE_FULLSCREEN = 3;

    const INITIAL_PDF_ZOOM_TYPE_FIT_WIDTH = 1;
    const INITIAL_PDF_ZOOM_TYPE_FIT_HEIGHT = 2;
    const INITIAL_PDF_ZOOM_TYPE_FIT_PAGE = 3;

    /*
     * Request options
     */

    /** @var array */
    protected $requestBody;

    /** @var int */
    protected $timeout;

    /** @var string */
    protected $user_agent;

    /** @var mixed */
    protected $output_destination;

    /** @var string */
    protected $proxy_name;

    /** @var int */
    protected $proxy_port;

    /** @var string  */
    protected $proxy_username = '';

    /** @var string  */
    protected $proxy_password = '';

    /*
     * Tracking tokens
     */

    /** @var bool  */
    protected $track_tokens = false;

    /** @var int */
    protected $num_tokens_before = false;

    /** @var FactoryInterface */
    protected $requestFactory;

    /**
     * Pdfcrowd constructor.
     *
     * @param string $username
     * @param string $key
     */
    public function __construct(string $username, string $key)
    {
        $this->requestBody = [
            'username' => $username,
            'key' => $key,
            'pdf_scaling_factor' => 1,
            'html_zoom' => 200,
        ];

        $this->user_agent = $this->getUserAgent();

        $this->requestFactory = new RequestFactory();
    }

    /**
     * This method allows you to override the default CurlRequest object. Added for testing purposes.
     *
     * @param \Swis\PdfcrowdClient\Http\FactoryInterface $requestFactory
     */
    public function setRequestFactory(FactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Each httpPost-call uses a clean request object.
     *
     * @return \Swis\PdfcrowdClient\Http\RequestInterface
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    protected function getNewRequestObject(): RequestInterface
    {
        $request = $this->requestFactory->create();

        return $request;
    }

    /**
     * Converts an in-memory html document.
     *
     * @param string $src       a string containing a html document
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function convertHtml($src)
    {
        if (!$src) {
            throw new PdfcrowdException('convertHTML(): the src parameter must not be empty');
        }

        $this->requestBody['src'] = $src;

        $uri = $this->getApiUri('/pdf/convert/html/');

        if ($this->track_tokens) {
            $this->num_tokens_before = $this->availableTokens();
        }

        return $this->httpPost($uri, $this->requestBody);
    }

    /**
     * Converts a web page.
     *
     * @param string $src       a web page URL
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function convertURI(string $src)
    {
        $src = trim($src);
        if (!preg_match("/^https?:\/\/.*/i", $src)) {
            throw new PdfcrowdException("convertURI(): the URL must start with http:// or https:// (got '$src')");
        }

        $this->requestBody['src'] = $src;
        $uri = $this->getApiUri('/pdf/convert/uri/');

        if ($this->track_tokens) {
            $this->num_tokens_before = $this->availableTokens();
        }

        return $this->httpPost($uri, $this->requestBody);
    }

    /**
     * Returns the number of available conversion tokens.
     *
     * @return int
     */
    public function availableTokens(): int
    {
        $username = $this->requestBody['username'];

        $uri = $this->getApiUri("/user/{$username}/tokens/");

        $arr = [
            'username' => $this->requestBody['username'],
            'key' => $this->requestBody['key'],
        ];

        $ntokens = $this->httpPost($uri, $arr);

        $response = (string) $ntokens;

        return (int) $response;
    }

    /**
     * Get the number of tokens used in the last conversion.
     * This is only possible if you enable tracking tokens using trackTokens(true).
     *
     * @see trackTokens()
     *
     * @return int
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function getUsedTokens(): int
    {
        if (!$this->track_tokens) {
            throw new PdfcrowdException(
                'getUsedTokens() only works if you enable tracking tokens with trackTokens(true)'
            );
        }

        if ($this->num_tokens_before === false) {
            throw new PdfcrowdException(
                'getUsedTokens() should not be called on its own, call a convert call first.'
            );
        }

        $num_tokens_after = $this->availableTokens();

        return (int) $this->num_tokens_before - $num_tokens_after;
    }

    /**
     * Track how many tokens are available before each request.
     * After a request you can ask the number of used tokens with getUsedTokens.
     *
     * @see getUsedTokens()
     *
     * @param bool $trackTokens
     */
    public function trackTokens(bool $trackTokens = true)
    {
        $this->track_tokens = $trackTokens;
    }

    /**
     * Save the pdf to the given output destination. The variable $file_handle will serve as input to
     * the sink-option of Guzzle.
     *
     * @see http://docs.guzzlephp.org/en/stable/request-options.html#sink
     *
     * @example $pdfcrowd->setOutputDestination(fopen('/path/to/output.pdf', 'w');
     *
     * @param $file_handle
     */
    public function setOutputDestination($file_handle)
    {
        $this->output_destination = $file_handle;
    }

    public function setPageWidth($value)
    {
        $this->requestBody['width'] = $value;
    }

    public function setPageHeight($value)
    {
        $this->requestBody['height'] = $value;
    }

    public function setHorizontalMargin($value)
    {
        $this->requestBody['margin_right'] = $this->requestBody['margin_left'] = $value;
    }

    public function setVerticalMargin($value)
    {
        $this->requestBody['margin_top'] = $this->requestBody['margin_bottom'] = $value;
    }

    public function setBottomMargin($value)
    {
        $this->requestBody['margin_bottom'] = $value;
    }

    public function setPageMargins($top, $right, $bottom, $left)
    {
        $this->requestBody['margin_top'] = $top;
        $this->requestBody['margin_right'] = $right;
        $this->requestBody['margin_bottom'] = $bottom;
        $this->requestBody['margin_left'] = $left;
    }

    /**
     * If value is set to True then the PDF is encrypted. This prevents search engines from indexing the document.
     * The default is False.
     *
     * @param bool $val
     */
    public function setEncrypted(bool $val = true)
    {
        $this->setOrUnset($val, 'encrypted');
    }

    /**
     * Protects the PDF with a user password. When a PDF has a user password, it must be supplied in order to view the
     * document and to perform operations allowed by the access permissions. At most 32 characters.
     *
     * @param string $pwd
     */
    public function setUserPassword(string $pwd)
    {
        $this->setOrUnset($pwd, 'user_pwd');
    }

    /**
     * Protects the PDF with an owner password. Supplying an owner password grants unlimited access to the PDF
     * including changing the passwords and access permissions. At most 32 characters.
     *
     * @param string $pwd
     */
    public function setOwnerPassword(string $pwd)
    {
        $this->setOrUnset($pwd, 'owner_pwd');
    }

    /**
     * Set value to True disables printing the generated PDF. The default is False.
     *
     * @param bool $val
     */
    public function setNoPrint(bool $val = true)
    {
        $this->setOrUnset($val, 'no_print');
    }

    /**
     * Set value to True to disable modifying the PDF. The default is False.
     *
     * @param bool $val
     */
    public function setNoModify(bool $val = true)
    {
        $this->setOrUnset($val, 'no_modify');
    }

    /**
     * Set value to True to disable extracting text and graphics from the PDF. The default is False.
     *
     * @param bool $val
     */
    public function setNoCopy(bool $val = true)
    {
        $this->setOrUnset($val, 'no_copy');
    }

    /**
     * Specifies the initial page layout when the PDF is opened in a viewer.
     *
     * Possible values:
     *   \Swis\PdfcrowdClient\Pdfcrowd::SINGLE_PAGE
     *   \Swis\PdfcrowdClient\Pdfcrowd::CONTINUOUS
     *   \Swis\PdfcrowdClient\Pdfcrowd::CONTINUOUS_FACING
     *
     * @param int $value
     *
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function setPageLayout(int $value)
    {
        if (!in_array($value, [
            self::PAGE_LAYOUT_SINGLE_PAGE,
            self::PAGE_LAYOUT_CONTINUOUS,
            self::PAGE_LAYOUT_CONTINUOUS_FACING
        ])) {
            throw new PdfcrowdException('Invalid page layout value!');
        }

        $this->requestBody['page_layout'] = $value;
    }

    /**
     * Specifies the appearance of the PDF when opened.
     *
     * Possible values:
     *   \Swis\PdfcrowdClient\Pdfcrowd::NONE_VISIBLE
     *   \Swis\PdfcrowdClient\Pdfcrowd::THUMBNAILS_VISIBLE
     *   \Swis\PdfcrowdClient\Pdfcrowd::PAGE_MODE_FULLSCREEN
     *
     * @param int $value
     *
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function setPageMode(int $value)
    {
        if (!in_array($value, [
            self::PAGE_MODE_NONE_VISIBLE,
            self::PAGE_MODE_THUMBNAILS_VISIBLE,
            self::PAGE_MODE_FULLSCREEN
        ])) {
            throw new PdfcrowdException('Invalid page mode value!');
        }

        $this->requestBody['page_mode'] = $value;
    }

    /**
     * @param string $value
     */
    public function setFooterText(string $value)
    {
        $this->setOrUnset($value, 'footer_text');
    }

    /**
     * Set value to False to disable printing images to the PDF. The default is True.
     *
     * @param bool $value
     */
    public function enableImages(bool $value = true)
    {
        $this->setOrUnset(!$value, 'no_images');
    }

    /**
     * Set value to False to disable printing backgrounds to the PDF. The default is True.
     *
     * @param bool $value
     */
    public function enableBackgrounds(bool $value = true)
    {
        $this->setOrUnset(!$value, 'no_backgrounds');
    }

    /**
     * Set HTML zoom in percents. It determines the precision used for rendering of the HTML content. Despite its name,
     * it does not zoom the HTML content. Higher values can improve glyph positioning and can lead to overall better
     * visual appearance of generated PDF .The default value is 200.
     *
     * @see setPdfScalingFactor
     *
     * @param int $value
     */
    public function setHtmlZoom(int $value)
    {
        $this->setOrUnset($value, 'html_zoom');
    }

    /**
     * Set value to False to disable JavaScript in web pages. The default is True.
     *
     * @param bool $value
     */
    public function enableJavaScript(bool $value = true)
    {
        $this->setOrUnset(!$value, 'no_javascript');
    }

    /**
     * Set value to False to disable hyperlinks in the PDF. The default is True.
     *
     * @param bool $value
     */
    public function enableHyperlinks(bool $value = true)
    {
        $this->setOrUnset(!$value, 'no_hyperlinks');
    }

    /**
     * Value is the text encoding used when none is specified in a web page. The default is utf-8.
     *
     * @param string $value
     */
    public function setDefaultTextEncoding(string $value)
    {
        $this->setOrUnset($value, 'text_encoding');
    }

    /**
     * If value is True then the print CSS media type is used (if available).
     *
     * @param bool $value
     */
    public function usePrintMedia(bool $value = true)
    {
        $this->setOrUnset($value, 'use_print_media');
    }

    /**
     * Prints at most npages pages.
     *
     * @param int $value
     */
    public function setMaxPages(int $value)
    {
        $this->requestBody['max_pages'] = $value;
    }

    /**
     * @param bool $value
     */
    public function enablePdfcrowdLogo(bool $value = true)
    {
        $this->setOrUnset($value, 'pdfcrowd_logo');
    }

    /**
     * value specifies the appearance of the PDF when opened.
     *
     * Possible values:
     *   \Swis\Pdfcrowd\Pdfcrowd::FIT_WIDTH
     *   \Swis\Pdfcrowd\Pdfcrowd::FIT_HEIGHT
     *   \Swis\Pdfcrowd\Pdfcrowd::FIT_PAGE
     *
     * @param int $value
     *
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function setInitialPdfZoomType(int $value)
    {
        if (!in_array($value, [
            self::INITIAL_PDF_ZOOM_TYPE_FIT_WIDTH,
            self::INITIAL_PDF_ZOOM_TYPE_FIT_HEIGHT,
            self::INITIAL_PDF_ZOOM_TYPE_FIT_PAGE,
        ])) {
            throw new PdfcrowdException('Invalid initial pdf zoom type value!');
        }

        $this->requestBody['initial_pdf_zoom_type'] = $value;
    }

    /**
     * value specifies the initial page zoom of the PDF when opened.
     *
     * @param $value
     */
    public function setInitialPdfExactZoom($value)
    {
        $this->requestBody['initial_pdf_zoom_type'] = 4;
        $this->requestBody['initial_pdf_zoom'] = $value;
    }

    /**
     * The scaling factor used to convert between HTML and PDF. The default value is 1.0.
     *
     * @param float $value
     */
    public function setPdfScalingFactor(float $value)
    {
        $this->requestBody['pdf_scaling_factor'] = $value;
    }

    /**
     * Sets the author field in the created PDF.
     *
     * @param string $value
     */
    public function setAuthor(string $value)
    {
        $this->requestBody['author'] = $value;
    }

    /**
     * If value is True then the conversion will fail when the source URI returns 4xx or 5xx HTTP status code. The
     * default is False.
     *
     * @param bool $value
     */
    public function setFailOnNon200(bool $value)
    {
        $this->requestBody['fail_on_non200'] = $value;
    }

    /**
     * Places the specified html code inside the page footer. The following variables are expanded:
     *   %u - URL to convert.
     *   %p - The current page number.
     *   %n - Total number of pages.
     *
     * @param string $value
     */
    public function setFooterHtml(string $value)
    {
        $this->requestBody['footer_html'] = $value;
    }

    /**
     * Loads HTML code from the specified url and places it inside the page footer. See setFooterHtml for the list of
     * variables that are expanded.
     *
     * @see setFooterHtml
     *
     * @param string $value
     */
    public function setFooterUrl(string $value)
    {
        $this->requestBody['footer_url'] = $value;
    }

    /**
     * Places the specified html code inside the page header. See setFooterHtml for the list of variables that are
     * expanded.
     *
     * @see setFooterHtml
     *
     * @param string $value
     */
    public function setHeaderHtml(string $value)
    {
        $this->requestBody['header_html'] = $value;
    }

    /**
     * Loads HTML code from the specified url and places it inside the page header. See setFooterHtml for the list of
     * variables that are expanded.
     *
     * @see setFooterHtml
     *
     * @param string $value
     */
    public function setHeaderUrl(string $value)
    {
        $this->requestBody['header_url'] = $value;
    }

    /**
     * The page background color in RRGGBB hexadecimal format.
     *
     * @param string $value
     */
    public function setPageBackgroundColor(string $value)
    {
        $this->requestBody['page_background_color'] = $value;
    }

    /**
     * Does not print the body background. Requires the following CSS rule to be declared:
     *   body {background-color:rgba(255,255,255,0.0);}
     *
     * @param bool $value
     */
    public function setTransparentBackground(bool $value = true)
    {
        $this->setOrUnset($value, 'transparent_background');
    }

    /**
     * An offset between physical and logical page numbers. The default value is 0.
     *
     * @example if set to "1" then the page numbering will start with 1 on the second page.
     *
     * @param int $value
     */
    public function setPageNumberingOffset(int $value)
    {
        $this->requestBody['page_numbering_offset'] = $value;
    }

    /**
     * Value is a comma seperated list of physical page numbers on which the header a footer are not printed. Negative
     * numbers count backwards from the last page: -1 is the last page, -2 is the last but one page, and so on.
     *
     * @example "1,-1" will not print the header and footer on the first and the last page.
     *
     * @param string $value
     */
    public function setHeaderFooterPageExcludeList(string $value)
    {
        $this->requestBody['header_footer_page_exclude_list'] = $value;
    }

    /**
     * url is a public absolute URL of the watermark image (must start either with http:// or https://). The supported
     * formats are PNG and JPEG. offset_x and offset_y is the watermark offset in units. The default offset is (0,0).
     *
     * @param string $url
     * @param int    $offset_x
     * @param int    $offset_y
     */
    public function setWatermark(string $url, $offset_x = 0, $offset_y = 0)
    {
        $this->requestBody['watermark_url'] = $url;
        $this->requestBody['watermark_offset_x'] = $offset_x;
        $this->requestBody['watermark_offset_y'] = $offset_y;
    }

    /**
     * Rotates the watermark by angle degrees.
     *
     * @param int $angle
     */
    public function setWatermarkRotationsetWatermarkRotation(int $angle)
    {
        $this->requestBody['watermark_rotation'] = $angle;
    }

    /**
     * When value is set to True then the watermark is be placed in the background. By default, the watermark is
     * placed in the foreground.
     *
     * @param bool $val
     */
    public function setWatermarkInBackground(bool $val = true)
    {
        $this->setOrUnset($val, 'watermark_in_background');
    }

    /**
     * @param string $proxyname
     * @param int    $port
     * @param string $username
     * @param string $password
     */
    public function setProxy(string $proxyname, int $port, string $username = '', string $password = '')
    {
        $this->proxy_name = $proxyname;
        $this->proxy_port = $port;
        $this->proxy_username = $username;
        $this->proxy_password = $password;
    }

    /**
     * Override the default user agent value that is sent with each request to the Pdfcrowd API.
     *
     * @param string $user_agent
     */
    public function setUserAgent(string $user_agent)
    {
        $this->user_agent = $user_agent;
    }

    /**
     * Set the timeout request option.
     *
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        if (is_int($timeout) && $timeout > 0) {
            $this->timeout = $timeout;
        }
    }

    /**
     * @param string $url
     * @param array  $requestBody
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    private function httpPost(string $url, array $requestBody)
    {
        $request = $this->buildRequest($url, $requestBody);

        try {
            $response = $request->execute();

            $http_code = $request->getHttpStatusCode();
        } catch (\Exception $e) {
            throw new PdfcrowdException("Unknown error during request to Pdfcrowd", 0, $e);
        }

        if ($http_code !== 200) {
            throw new PdfcrowdException((string) $response, $http_code);
        }

        return $response;
    }

    protected function buildRequest(string $url, array $requestBody): RequestInterface
    {
        $request = $this->getNewRequestObject();

        $request->setUserAgent($this->user_agent);

        if (isset($this->timeout)) {
            $request->setTimeout($this->timeout);
        }

        if ($this->proxy_name) {
            $request->setProxy($this->proxy_name, $this->proxy_port);
            if ($this->proxy_username) {
                $request->setProxyAuth($this->proxy_username, $this->proxy_password);
            }
        }

        $request->setUrl($url);

        $request->setBody($requestBody);

        if (isset($this->output_destination)) {
            $request->setOutputDestination($this->output_destination);
        }

        return $request;
    }

    /**
     * Set or unset a parameter that will be sent with the request to the pdfcrowd API.
     *
     * @param mixed $val
     * @param string $field
     */
    private function setOrUnset($val, string $field)
    {
        if ($val) {
            $this->requestBody[$field] = $val;
        } else {
            unset($this->requestBody[$field]);
        }
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    protected function getApiUri(string $endpoint): string
    {
        return self::API_PREFIX.$endpoint;
    }

    protected function getUserAgent()
    {
        return 'swisnl_pdfcrowd_client_'.self::CLIENT_VERSION;
    }
}
