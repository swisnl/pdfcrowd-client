<?php

declare(strict_types=1);

namespace Swis\PdfcrowdClient;

use CURLFile;
use Swis\PdfcrowdClient\Exceptions\PdfcrowdException;

class Pdfcrowd
{
    private $fields;
    private $scheme;
    private $port;
    private $api_prefix;
    private $curlopt_timeout;
    private $hostname;
    private $user_agent;
    private $num_tokens_before;
    private $http_code;
    private $error;
    private $outstream;

    public static $client_version = '2.7';
    public static $http_port = 80;
    public static $https_port = 443;
    public static $api_host = 'pdfcrowd.com';

    private $proxy_name = null;
    private $proxy_port = null;
    private $proxy_username = '';
    private $proxy_password = '';

    // constants for setPageLayout()
    const SINGLE_PAGE = 1;
    const CONTINUOUS = 2;
    const CONTINUOUS_FACING = 3;

    // constants for setPageMode()
    const NONE_VISIBLE = 1;
    const THUMBNAILS_VISIBLE = 2;
    const FULLSCREEN = 3;

    // constants for setInitialPdfZoomType()
    const FIT_WIDTH = 1;
    const FIT_HEIGHT = 2;
    const FIT_PAGE = 3;

    /**
     * Pdfcrowd constructor.
     *
     * @param string $username
     * @param string $key
     */
    public function __construct(string $username, string $key)
    {
        $this->hostname = self::$api_host;

        $this->useSSL(true);

        $this->fields = [
            'username' => $username,
            'key' => $key,
            'pdf_scaling_factor' => 1,
            'html_zoom' => 200,
        ];

        $this->user_agent = 'pdfcrowd_php_client_'.self::$client_version.'_(http://pdfcrowd.com)';
    }

    /**
     * Converts an in-memory html document.
     *
     * @param string $src       a string containing a html document
     * @param null   $outstream output stream, if null then the return value is a string containing the PDF
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function convertHtml($src, $outstream = null)
    {
        if (!$src) {
            throw new PdfcrowdException('convertHTML(): the src parameter must not be empty');
        }

        $this->fields['src'] = $src;
        $uri = $this->api_prefix.'/pdf/convert/html/';
        $postfields = http_build_query($this->fields, '', '&');

        $this->num_tokens_before = $this->numTokens();

        return $this->http_post($uri, $postfields, $outstream);
    }

    /**
     * Converts an in-memory html document.
     *
     * @param string $src       a path to an html file
     * @param null   $outstream output stream, if null then the return value is a string containing the PDF
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function convertFile(string $src, $outstream = null)
    {
        $src = trim($src);

        if (!file_exists($src)) {
            $cwd = getcwd();
            throw new PdfcrowdException("convertFile(): '{$src}' not found
                        Possible reasons:
                         1. The file is missing.
                         2. You misspelled the file name.
                         3. You use a relative file path (e.g. 'index.html') but the current working
                            directory is somewhere else than you expect: '${cwd}'
                            Generally, it is safer to use an absolute file path instead of a relative one.
                        ");
        }

        if (is_dir($src)) {
            throw new PdfcrowdException("convertFile(): '{$src}' must be file, not a directory");
        }

        if (!is_readable($src)) {
            throw new PdfcrowdException("convertFile(): cannot read '{$src}', please check if the process has sufficient permissions");
        }

        if (!filesize($src)) {
            throw new PdfcrowdException("convertFile(): '{$src}' must not be empty");
        }

        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $this->fields['src'] = new CurlFile($src);
        } else {
            $this->fields['src'] = '@'.$src;
        }

        $uri = $this->api_prefix.'/pdf/convert/html/';

        $this->num_tokens_before = $this->numTokens();

        return $this->http_post($uri, $this->fields, $outstream);
    }

    /**
     * Converts a web page.
     *
     * @param string $src       a web page URL
     * @param null   $outstream output stream, if null then the return value is a string containing the PDF
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    public function convertURI(string $src, $outstream = null)
    {
        $src = trim($src);
        if (!preg_match("/^https?:\/\/.*/i", $src)) {
            throw new PdfcrowdException("convertURI(): the URL must start with http:// or https:// (got '$src')");
        }

        $this->fields['src'] = $src;
        $uri = $this->api_prefix.'/pdf/convert/uri/';
        $postfields = http_build_query($this->fields, '', '&');

        $this->num_tokens_before = $this->numTokens();

        return $this->http_post($uri, $postfields, $outstream);
    }

    /**
     * Returns the number of available conversion tokens.
     *
     * @return int
     */
    public function numTokens(): int
    {
        $username = $this->fields['username'];
        $uri = $this->api_prefix."/user/{$username}/tokens/";
        $arr = ['username' => $this->fields['username'],
                'key' => $this->fields['key'], ];
        $postfields = http_build_query($arr, '', '&');
        $ntokens = $this->http_post($uri, $postfields, null);

        return (int) $ntokens;
    }

    /**
     * Get the number of tokens used in the last conversion
     *
     * @return int
     */
    public function getUsedTokens(): int
    {
        $num_tokens_after = $this->numTokens();

        return (int) $this->num_tokens_before - $num_tokens_after;
    }

    /**
     * Turn SSL on or off.
     *
     * @param bool $use_ssl
     */
    public function useSSL(bool $use_ssl)
    {
        if ($use_ssl) {
            $this->port = self::$https_port;
            $this->scheme = 'https';
        } else {
            $this->port = self::$http_port;
            $this->scheme = 'http';
        }

        $this->api_prefix = "{$this->scheme}://{$this->hostname}/api";
    }

    public function setPageWidth($value)
    {
        $this->fields['width'] = $value;
    }

    public function setPageHeight($value)
    {
        $this->fields['height'] = $value;
    }

    public function setHorizontalMargin($value)
    {
        $this->fields['margin_right'] = $this->fields['margin_left'] = $value;
    }

    public function setVerticalMargin($value)
    {
        $this->fields['margin_top'] = $this->fields['margin_bottom'] = $value;
    }

    public function setBottomMargin($value)
    {
        $this->fields['margin_bottom'] = $value;
    }

    public function setPageMargins($top, $right, $bottom, $left)
    {
        $this->fields['margin_top'] = $top;
        $this->fields['margin_right'] = $right;
        $this->fields['margin_bottom'] = $bottom;
        $this->fields['margin_left'] = $left;
    }

    /**
     * If value is set to True then the PDF is encrypted. This prevents search engines from indexing the document.
     * The default is False.
     *
     * @param bool $val
     */
    public function setEncrypted(bool $val = true)
    {
        $this->set_or_unset($val, 'encrypted');
    }

    /**
     * Protects the PDF with a user password. When a PDF has a user password, it must be supplied in order to view the
     * document and to perform operations allowed by the access permissions. At most 32 characters.
     *
     * @param string $pwd
     */
    public function setUserPassword(string $pwd)
    {
        $this->set_or_unset($pwd, 'user_pwd');
    }

    /**
     * Protects the PDF with an owner password. Supplying an owner password grants unlimited access to the PDF
     * including changing the passwords and access permissions. At most 32 characters.
     *
     * @param string $pwd
     */
    public function setOwnerPassword(string $pwd)
    {
        $this->set_or_unset($pwd, 'owner_pwd');
    }

    /**
     * Set value to True disables printing the generated PDF. The default is False.
     *
     * @param bool $val
     */
    public function setNoPrint(bool $val = true)
    {
        $this->set_or_unset($val, 'no_print');
    }

    /**
     * Set value to True to disable modifying the PDF. The default is False.
     *
     * @param bool $val
     */
    public function setNoModify(bool $val = true)
    {
        $this->set_or_unset($val, 'no_modify');
    }

    /**
     * Set value to True to disable extracting text and graphics from the PDF. The default is False.
     *
     * @param bool $val
     */
    public function setNoCopy(bool $val = true)
    {
        $this->set_or_unset($val, 'no_copy');
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
     */
    public function setPageLayout(int $value)
    {
        assert($value > 0 && $value <= 3);
        $this->fields['page_layout'] = $value;
    }

    /**
     * Specifies the appearance of the PDF when opened.
     *
     * Possible values:
     *   \Swis\PdfcrowdClient\Pdfcrowd::NONE_VISIBLE
     *   \Swis\PdfcrowdClient\Pdfcrowd::THUMBNAILS_VISIBLE
     *   \Swis\PdfcrowdClient\Pdfcrowd::FULLSCREEN
     *
     * @param int $value
     */
    public function setPageMode(int $value)
    {
        assert($value > 0 && $value <= 3);
        $this->fields['page_mode'] = $value;
    }

    /**
     * @param string $value
     */
    public function setFooterText(string $value)
    {
        $this->set_or_unset($value, 'footer_text');
    }

    /**
     * Set value to False to disable printing images to the PDF. The default is True.
     *
     * @param bool $value
     */
    public function enableImages(bool $value = true)
    {
        $this->set_or_unset(!$value, 'no_images');
    }

    /**
     * Set value to False to disable printing backgrounds to the PDF. The default is True.
     *
     * @param bool $value
     */
    public function enableBackgrounds(bool $value = true)
    {
        $this->set_or_unset(!$value, 'no_backgrounds');
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
        $this->set_or_unset($value, 'html_zoom');
    }

    /**
     * Set value to False to disable JavaScript in web pages. The default is True.
     *
     * @param bool $value
     */
    public function enableJavaScript(bool $value = true)
    {
        $this->set_or_unset(!$value, 'no_javascript');
    }

    /**
     * Set value to False to disable hyperlinks in the PDF. The default is True.
     *
     * @param bool $value
     */
    public function enableHyperlinks(bool $value = true)
    {
        $this->set_or_unset(!$value, 'no_hyperlinks');
    }

    /**
     * Value is the text encoding used when none is specified in a web page. The default is utf-8.
     *
     * @param string $value
     */
    public function setDefaultTextEncoding(string $value)
    {
        $this->set_or_unset($value, 'text_encoding');
    }

    /**
     * If value is True then the print CSS media type is used (if available).
     *
     * @param bool $value
     */
    public function usePrintMedia(bool $value = true)
    {
        $this->set_or_unset($value, 'use_print_media');
    }

    /**
     * Prints at most npages pages.
     *
     * @param int $value
     */
    public function setMaxPages(int $value)
    {
        $this->fields['max_pages'] = $value;
    }

    /**
     * @param bool $value
     */
    public function enablePdfcrowdLogo(bool $value = true)
    {
        $this->set_or_unset($value, 'pdfcrowd_logo');
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
     */
    public function setInitialPdfZoomType(int $value)
    {
        assert($value > 0 && $value <= 3);
        $this->fields['initial_pdf_zoom_type'] = $value;
    }

    /**
     * value specifies the initial page zoom of the PDF when opened.
     *
     * @param $value
     */
    public function setInitialPdfExactZoom($value)
    {
        $this->fields['initial_pdf_zoom_type'] = 4;
        $this->fields['initial_pdf_zoom'] = $value;
    }

    /**
     * The scaling factor used to convert between HTML and PDF. The default value is 1.0.
     *
     * @param float $value
     */
    public function setPdfScalingFactor(float $value)
    {
        $this->fields['pdf_scaling_factor'] = $value;
    }

    /**
     * Sets the author field in the created PDF.
     *
     * @param string $value
     */
    public function setAuthor(string $value)
    {
        $this->fields['author'] = $value;
    }

    /**
     * If value is True then the conversion will fail when the source URI returns 4xx or 5xx HTTP status code. The
     * default is False.
     *
     * @param bool $value
     */
    public function setFailOnNon200(bool $value)
    {
        $this->fields['fail_on_non200'] = $value;
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
        $this->fields['footer_html'] = $value;
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
        $this->fields['footer_url'] = $value;
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
        $this->fields['header_html'] = $value;
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
        $this->fields['header_url'] = $value;
    }

    /**
     * The page background color in RRGGBB hexadecimal format.
     *
     * @param string $value
     */
    public function setPageBackgroundColor(string $value)
    {
        $this->fields['page_background_color'] = $value;
    }

    /**
     * Does not print the body background. Requires the following CSS rule to be declared:
     *   body {background-color:rgba(255,255,255,0.0);}
     *
     * @param bool $value
     */
    public function setTransparentBackground(bool $value = true)
    {
        $this->set_or_unset($value, 'transparent_background');
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
        $this->fields['page_numbering_offset'] = $value;
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
        $this->fields['header_footer_page_exclude_list'] = $value;
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
        $this->fields['watermark_url'] = $url;
        $this->fields['watermark_offset_x'] = $offset_x;
        $this->fields['watermark_offset_y'] = $offset_y;
    }

    /**
     * Rotates the watermark by angle degrees.
     *
     * @param int $angle
     */
    public function setWatermarkRotationsetWatermarkRotation(int $angle)
    {
        $this->fields['watermark_rotation'] = $angle;
    }

    /**
     * When value is set to True then the watermark is be placed in the background. By default, the watermark is
     * placed in the foreground.
     *
     * @param bool $val
     */
    public function setWatermarkInBackground(bool $val = true)
    {
        $this->set_or_unset($val, 'watermark_in_background');
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
     * @param string $user_agent
     */
    public function setUserAgent(string $user_agent)
    {
        $this->user_agent = $user_agent;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        if (is_int($timeout) && $timeout > 0) {
            $this->curlopt_timeout = $timeout;
        }
    }

    /**
     * @param string $url
     * @param        $postfields
     * @param        $outstream
     *
     * @return mixed
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    private function http_post(string $url, $postfields, $outstream)
    {
        // todo: add curl to dependencies
        if (!function_exists('curl_init')) {
            throw new PdfcrowdException('Curl php extension missing');
        }

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_PORT, $this->port);
        curl_setopt($c, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($c, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($c, CURLOPT_USERAGENT, $this->user_agent);
        if (isset($this->curlopt_timeout)) {
            curl_setopt($c, CURLOPT_TIMEOUT, $this->curlopt_timeout);
        }
        if ($outstream) {
            $this->outstream = $outstream;
            curl_setopt($c, CURLOPT_WRITEFUNCTION, [$this, 'receive_to_stream']);
        }

        if ($this->scheme == 'https' && self::$api_host == 'pdfcrowd.com') {
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ($this->proxy_name) {
            curl_setopt($c, CURLOPT_PROXY, $this->proxy_name.':'.$this->proxy_port);
            if ($this->proxy_username) {
                curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($c, CURLOPT_PROXYUSERPWD, $this->proxy_username.':'.$this->proxy_password);
            }
        }

        $this->http_code = 0;
        $this->error = '';

        $response = curl_exec($c);
        $this->http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $error_str = curl_error($c);
        $error_nr = curl_errno($c);
        curl_close($c);

        if ($error_nr != 0) {
            throw new PdfcrowdException($error_str, $error_nr);
        } elseif ($this->http_code == 200) {
            if ($outstream == null) {
                return $response;
            }
        }

        throw new PdfcrowdException($this->error ? $this->error : $response, $this->http_code);
    }

    /**
     * @see http://php.net/manual/en/function.curl-setopt.php and look for CURLOPT_WRITEFUNCTION
     *
     * @param $curl
     * @param $data
     *
     * @return bool|int
     * @throws \Swis\PdfcrowdClient\Exceptions\PdfcrowdException
     */
    private function receive_to_stream($curl, $data)
    {
        if ($this->http_code == 0) {
            $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        if ($this->http_code >= 400) {
            $this->error = $this->error.$data;

            return strlen($data);
        }

        $written = fwrite($this->outstream, $data);
        if ($written != strlen($data)) {
            if (get_magic_quotes_runtime()) {
                throw new PdfcrowdException("Cannot write the PDF file because the 'magic_quotes_runtime' setting is enabled.
                            Please disable it either in your php.ini file, or in your code by calling 'set_magic_quotes_runtime(false)'.");
            } else {
                throw new PdfcrowdException('Writing the PDF file failed. The disk may be full.');
            }
        }

        return $written;
    }

    /**
     * Set or unset a parameter that will be sent with the request to the pdfcrowd API.
     *
     * @param mixed $val
     * @param string $field
     */
    private function set_or_unset($val, string $field)
    {
        if ($val) {
            $this->fields[$field] = $val;
        } else {
            unset($this->fields[$field]);
        }
    }
}
