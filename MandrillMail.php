<?php
/**
 * Mandrill Mail Wrapper for common Mandrill email messages
 * @author Mike Seiler <michaelseiler.net>
 * @version 1.0
 */

class MandrillMail
{

    /**
     * Mandrill API Key
     *
     * @const apiKey
     */
    private $apiKey = 'your_api_key_from_mandrill';

    /**
     * Mandrill Test API Key
     * @const apiTestKey
     */
    private $apiTestKey = 'your_test_api_key_from_mandrill';

    /**
     * Mandrill API Object
     *
     * @var object
     */
    public $mandrill;

    /**
     * Mandrill $options Array of default Mandrill message
     * To override default options, use setOption($key,$value) in controller
     *
     * @var array
     */
    public $options = [
        'html' => null,
        'text' => null,
        'subject' => null,
        'from_email' => null,
        'from_name' => null,
        'to' => [],
        'headers' => [],
        'important' => false,
        'track_opens' => null,
        'track_clicks' => null,
        'auto_text' => null,
        'auto_html' => null,
        'inline_css' => null,
        'url_strip_qs' => null,
        'preserve_recipients' => null,
        'view_content_link' => null,
        'bcc_address' => null,
        'tracking_domain' => null,
        'signing_domain' => null,
        'return_path_domain' => null,
        'merge' => true,
        'merge_language' => 'mailchimp',
        'global_merge_vars' => null,
        'merge_vars' => null,
        'tags' => [],
        'google_analytics_domains' => ['example.com'],
        'google_analytics_campaign' => 'gac@example.com',
        'metadata' => ['website' => 'example.com'],
        'recipient_metadata' => null,
        'attachments' => null,
        'images' => null
    ];

    /**
     * Mandrill $async - default is false, which sends immediately and returns
     * a success/failure response; true queues a message - no response returned
     *
     * @var boolean
     */
    protected $async = false;

    /**
     * Mandrill $ipPool - if you do not have any dedicated IP addresses, this
     * variable has no effect
     *
     * @var string
     */
    protected $ipPool;

    /**
     * Mandrill $sendAt
     *
     * @var string Date in format of "Y-m-d H:i:s"
     */
    protected $sendAt;

    /**
     * Constructor
     *
     * @param boolean $setTestMode Sets Test mode
     */
    public function __construct(bool $setTestMode = null)
    {
        $apiKey = isset($setTestMode) ? $this->apiTestKey : $this->apiKey;
        $this->mandrill = new Mandrill($apiKey);
        // Set defaults - can be overridden with methods below
        self::setAsync($this->async);
        self::setIpPool($this->ipPool);
        self::setSendAt(date("Y-m-d H:i:s"));
    }

    /**
     * Sets an option based on key and value; allows overriding default values
     *
     * @param string $option The key value of the default $options array above
     * @param mixed $value The value; can be string, array, or boolean
     */
    public function setOption(string $option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Sets send at option so Mandrill can send at a later time
     * UTC timestamp in YYYY-MM-DD HH:MM:SS format; past date sends immediately
     *
     * @param string $date Date string in PHP "Y-m-d H:i:s" format
     */
    public function setSendAt(string $date)
    {
        $this->sendAt = $date;
    }

    /**
     * Sets Asynchronous mode
     * Set false if you need immediate notice of rejection or delievery
     *
     * @param  boolean
     */
    public function setAsync(bool $flag)
    {
        $this->async = $flag;
    }

    /**
     * Determines the IP Pool of servers to mail from
     * If you do not have any dedicated IPs, this parameter has no effect.
     *
     * @param string $ipPool Name of the dedicated ip pool to use
     */
    public function setIpPool(string $ipPool = null)
    {
        $this->ipPool = isset($ipPool) ? $ipPool : 'Main Pool';
    }

    /**
     * Sets HTML Body Text
     *
     * @param  string
     */
    public function setBodyHtml(string $html)
    {
        $this->options['html'] = $html;
    }

    /**
     * Sets Text Body Text
     *
     * @param  string
     */
    public function setBodyText(string $text)
    {
        $this->options['text'] = $text;
    }

    /**
     * Sets Subject of the Email
     *
     * @param string $subject Subject of Email
     */
    public function setSubject(string $subject)
    {
        $this->options['subject'] = $subject;
    }

    /**
     * Sets From fields in Mandrill Array based on headers
     *
     * @param string $email Email address of sender
     * @param string $name Name of the sender
     */
    public function setFrom(string $email, string $name = '')
    {
        $this->options['from_name'] = $name;
        $this->options['from_email'] = $email;
    }

    /**
     * Sets Reply-To array
     *
     * @param string $headerType Type of header to add
     * @param string $headerValue Value of added header type
     */
    public function addHeader(string $headerType, string $headerValue)
    {
        $this->options['headers'] = [$headerType => $headerValue];
    }

    /**
     * Sets the To items in Mandrill, according to the specs
     *
     * @param string $email Email of recipient
     * @param string $name Name of recipient
     * @param string $type Either To: CC: or BCC:
     *
     */
    public function addTo(string $email, string $name = '', string $type = null)
    {
        $personInfo['name'] = $name;
        $personInfo['email'] = $email;
        // default to "to" if "cc" or "bcc" is not set (use lowercase)
        $personInfo['type'] = isset($type) ? $type : 'to';
        $this->options['to'][] = $personInfo;
    }

    /**
     * Adds an attachment to Mandrill email
     *
     * @param string $type Mime type of the attachment
     * @param string $name Name of the attachment
     * @param string $content Base64 encoded body of the attachment
     */
    public function addAttachment(string $type, string $name, string $content)
    {
        $this->options['attachments'][] = [
            'type'=>$type,
            'name'=>$name,
            'content'=>base64_encode($content)
        ];
    }

    /**
     * Adds an image to Mandrill email
     *
     * @param string $name Name of the attachment
     * @param string $contentPath path to the image
     */
    public function addImage($name, $contentPath)
    {
        $data = file_get_contents($contentPath);
        $type = pathinfo($contentPath, PATHINFO_EXTENSION);
        $this->options['images'][] = [
            'type'=>$type,
            'name'=>$name,
            'content'=>base64_encode($data)
        ];
    }

    /**
     * Sets Global Merge Vars by appeding to the array
     *
     * @param string $name Name of Merge Var
     * @param string $value Value of Merge Var
     */
    public function setGlobalMergeVars(string $name, string $value)
    {
        $this->options['global_merge_vars'][] = ["name" => $name, "content" => $value];
    }
    /**
     * Sets the merge vars for sending via template
     *
     * @param string $recipient Who the mergeVars relate to
     * @param array $mergeVars An array of variables to merge based on Template requirements
     */
    public function setMergeVars(string $recipient, array $mergeVars)
    {
        $this->options['merge_vars'][] = ["rcpt" => $recipient, "vars" => $mergeVars];
    }

    /**
     * Sends Email array via Mandrill API
     *
     * @throws Exception of an extraordinary kind
     */
    public function send()
    {
        try {
            $result = $this->mandrill->messages->send(
                $this->options,
                $this->async,
                $this->ipPool,
                $this->sendAt
            );
            return $result;
        } catch (Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo "Mandrill Error: ".$e->getMessage();
        }
    }

    /**
     * Sends Email via template
     * sendTemplate($template_name, $template_content, $message, $async=false, $ip_pool=null, $send_at=null)
     * @param string $templateName The slug in Mandrill
     * @param array $templateContent The Content to send - none if you have a template that only uses merge vars
     */
    public function sendTemplate(string $templateName, array $templateContent)
    {
        try {
            $result = $this->mandrill->messages->sendTemplate(
                $templateName,
                $templateContent,
                $this->options,
                $this->async,
                $this->ipPool,
                $this->sendAt
            );
            return $result;
        } catch (Mandrill_Error $e) {
            echo "Mandrill Error: ".$e->getMessage();
        }
    }
}
