# MandrillMailer
Wrapper for Mandrill transactional email API from Mailchimp

## Usage
Store your production and test APIKeys in the MandrillMailer (or in a configuration
file elswere and reference the constant in MandrillMailer). The default object
uses the production key; to use the test key, pass 'true' as the argument.

**Production**

$mail = new MandrillMail();

**Test**

$mail = new MandrillMail(true);

**Optional: Setting additional API Paramenters**
```php
$mail->setIpPool('some pool');
$mail->setAsync(true);
$mail->setSendAt('2017-12-25 01:01:01');
```

**Sending a basic message**

```php
$mail->addTo("email@example.com", "Firstname Lastname");
$mail->setFrom("email@example.com", "Firstname Lastname");
$mail->addHeader("Reply-To", "sender@example.com");
$mail->setSubject("Example Subject");
$mail->setBodyText("Text of the email");
$mail->setBodyHtml("<h1>Example!</h1><p>I wanted you to see this.</p>");
$mail->send();
```

**Sending a message via template**

```php
$mail->addTo("email@example.com", "Firstname Lastname");
$mail->setFrom("email@example.com", "Firstname Lastname");
$mail->addHeader("Reply-To", "sender@example.com");
$mail->setSubject("Example Subject");
$mail->setBodyText("Text of the email");
$mail->setBodyHtml("<h1>Example!</h1><p>I wanted you to see this.</p>");
// Global merge vars are the same for everyone in the template
// The template will give you the names of the replacement variables
// e.g. *|ARCHIVE|*
$mail->setGlobalMergeVars("archive", "http://url.togoto.com");
$mail->setGlobalMergeVars("current_year", "2017");
$mail->setGlobalMergeVars("unsub", "http://url.tounsubscribe.at");
// Merge vars are specific variables for each recipient
// e.g. *|FNAME|*
// Most likely you'll do this in a loop
$mergeVars = [
    ["name"=>"fname","content"=>"Recipient first name"],
    ["name"=>"lname","content"=>"Recipient last name"]
];
$mail->setMergeVars("recipient1@example.com", $mergeVars);

$mergeVars1 = [
    ["name"=>"fname","content"=>"Recipient 2 first name"],
    ["name"=>"lname","content"=>"Recipient 2 last name"]
];
$mail->setMergeVars("recipient2@example.com", $mergeVars1);

// The array is the content you expect to send via template, but should 
// be empty if you have a template at Mandrill that has content built in
// and only needs to have the merge_vars swapped with your values
$mail->sendTemplate("name_of_template_at_mandrill", array());
```

**Adding Attachments**

If you use the addImage() method, the image will get added inline, and you'll have
to reference the id of the image in your inline HTML. If you add an image or file
as an attachment, it will display in email as a regular attachment.
```php
$file = file_get_contents("public/images/my_image.png");
$mail->addImage("image/png", "my_image.png", $file);
// Adding it for use inline in message:
$mail->setBodyHtml("<h1>Hello</h1><p>Here's an image.<img src='cid:my_image.png'></p>");
// Adding an image/pdf/file as an attachment
$mail->addAttachment("image/png", "my_image.png", $file);
```
