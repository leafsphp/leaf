<?php

namespace Leaf;

use \Leaf\Mail\SMTP;

/**
 * Leaf Mail
 * ------------------------------
 * Leaf PHP Framework's implementation of PHPMailer\PHPMailer
 * 
 * @author Michael Darko
 * @version 1.0.0
 * @since 2.0.0
 */
class Mail extends \PHPMailer\PHPMailer\PHPMailer
{
	private $errs;

	public function __construct(String $host = null, int $port = null, array $auth = [], bool $debug = true, bool $server_debug = false)
	{
		parent::__construct($debug);

		if ($host) {
			$this->smtp_connect($host, $port, empty($auth) ? false : true, empty($auth) ? null : $auth["username"], empty($auth) ? null : $auth["password"]);
		}

		if ($server_debug == true) {
			$this->smtp_debug("SERVER");
		}
		$this->form = new Form;
	}

	public function smtp_debug($debug = "SERVER")
	{
		if ($debug == "SERVER") {
			$this->SMTPDebug = SMTP::DEBUG_SERVER;
		} else {
			$this->SMTPDebug = $debug;
		}
	}

	/**
	 * Quickly Initialise a new SMTP connection
	 */
	public function smtp_connect(String $host, int $port, bool $uses_auth = false, String $username = null, String $password = null, $security = "STARTTLS")
	{
		$this->isSMTP();
		$this->Host = $host;
		$this->Port = $port;
		if ($uses_auth == true) {
			$this->SMTPAuth = true;
			$this->Username = $username;
			$this->Password = $password;
		}
		if ($security == "STARTTLS") {
			$this->SMTPSecure = self::ENCRYPTION_STARTTLS;
		} else {
			$this->SMTPSecure = $security;
		}
	}

	/**
	 * Send a basic email
	 */
	public function basic(String $subject, String $content, String $recepient_email, String $sender_name, String $sender_email = null, String $cc = null, String $bcc = null)
	{
		if ($sender_email == null) {
			$sender_email = $this->Username;
		}

		if (!$this->form->isEmail($recepient_email)) {
			$this->errs = "Recepient email not valid";
			return false;
		}

		if ($sender_email != null && !$this->form->isEmail($sender_email)) {
			$this->errs = "Sender email not valid";
			return false;
		}

		$this->Subject = $subject;
		$this->isHTML(true);
		$this->Body = $content;
		$this->addAddress($recepient_email);
		$this->setFrom($sender_email, $sender_name);

		if ($cc) {
			$this->addCC($cc);
		}

		if ($bcc) {
			$this->addBCC($bcc);
		}

		return $this;
	}

	/**
	 * Quickly write an email
	 * 
	 * Use these parameters in your email array
	 * - subject
	 * - body | template
	 * - recepient_email
	 * - sender_name
	 * - sender_email
	 * - attachment
	 * - cc
	 * - bcc
	 */
	public function write(array $email)
	{
		if (!isset($email["sender_email"])) {
			$email["sender_email"] = $this->Username;
		}

		if (isset($email["recepient_email"]) && !$this->form->isEmail($email["recepient_email"])) {
			$this->errs = "Recepient email not valid";
			return false;
		}

		if (isset($email["sender_email"]) && !$this->form->isEmail($email["sender_email"])) {
			$this->errs = "Sender email not valid";
			return false;
		}

		if (isset($email["cc"])) {
			$this->addCC($email["cc"]);
		}

		if (isset($email["bcc"])) {
			$this->addBCC($email["bcc"]);
		}

		if (isset($email["subject"])) {
			$this->Subject = $email["subject"];
		} else {
			$this->errs = "Subject (subject) is required";
			return false;
		}

		if (isset($email["body"])) {
			$this->isHTML(true);
			$this->Body = $email["body"];
		}

		if (isset($email["recepient_email"])) {
			$this->addAddress($email["recepient_email"]);
		} else {
			$this->errs = "Recepient email (recepient_email) is required";
			return false;
		}

		if (isset($email["attachment"])) {
			$this->addAttachment($email["attachment"]);
		}

		if (isset($email["template"])) {
			$this->loadTemplate($email["template"]);
		}

		if (isset($email["sender_name"])) {
			$this->setFrom($email["sender_email"], $email["sender_name"]);
		} else {
			$this->errs = "Sender name (sender_name) is required";
			return false;
		}

		return $this;
	}

	/**
	 * Load an already prepared template(file) as email body
	 */
	public function loadTemplate(String $path, bool $isReturned = false)
	{
		$this->isHTML(true);
		$this->Body = file_get_contents($path);
		if ($isReturned == true) {
			return $this->Body;
		}
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 * Never use a user-supplied path to a file!
	 * Returns false if the file could not be found or read.
	 * Explicitly *does not* support passing URLs; PHPMailer is not an HTTP client.
	 * If you need to do that, fetch the resource yourself and pass it in via a local file or string.
	 *
	 * @param string $path        Path to the attachment
	 * @param string $name        Overrides the attachment name
	 * @param string $encoding    File encoding (see $Encoding)
	 * @param string $type        File extension (MIME) type
	 * @param string $disposition Disposition to use
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	public function attach($path, $name = "", $encoding = parent::ENCODING_BASE64, $type = '', $disposition = 'attachment')
	{
		return $this->addAttachment($path, $name, $encoding, $type, $disposition);
	}

	/**
	 * Finalise and send an email
	 */
	// public function send() {
	// 	if (strlen($this->Subject) == 0) {
	// 		$this->errs = "Email Subject not found, this is required";
	// 		return false;
	// 	}
	// }

	/**
	 * Return any errors
	 */
	public function errors()
	{
		return $this->ErrorInfo . $this->errs;
	}
}
