<?php
  
  class Email {
    
    const PLAINTEXT_MAXIMUM_LENGTH = 79; // RFC 2646
    
    public static $oBNETDocsRecipient;
    
    private $sSubject;
    private $aRecipients;
    private $aMessageFlavors;
    
    public function __construct() {
      $this->sSubject        = '';
      $this->aRecipients     = array();
      $this->aMessageFlavors = array();
    }
    
    public function fAddRecipient(EmailRecipient &$oRecipient) {
      $this->aRecipients[] = $oRecipient;
    }
    
    public function fAddMessage(EmailMessage &$oMessage) {
      $this->aMessageFlavors[] = $oMessage;
    }
    
    public function fGetSubject() {
      return $this->sSubject;
    }
    
    public function fSend() {
      if (empty($this->sSubject))
        throw new RecoverableException('There is no subject for this email');
      if (!count($this->aRecipients))
        throw new RecoverableException('There are no recipients for this email');
      if (!count($this->aMessageFlavors))
        throw new RecoverableException('There are no message flavors for this email');
      
      // Build headers:
      
      $sHeaders = '';
      
      $aFrom = array();
      $aTo   = array();
      $aCc   = array();
      $aBcc  = array();
      
      foreach ($this->aRecipients as $oRecipient) {
        switch ($oRecipient->fGetType()) {
          case EmailRecipient::TYPE_FROM:
            $aFrom[] = $oRecipient->__toString(); break;
          case EmailRecipient::TYPE_TO:
            $aTo[]   = $oRecipient->__toString(); break;
          case EmailRecipient::TYPE_CC:
            $aCc[]   = $oRecipient->__toString(); break;
          case EmailRecipient::TYPE_BCC:
            $aBcc[]  = $oRecipient->__toString(); break;
        }
      }
      
      if ($aFrom)
        $sHeaders .= 'From: ' . implode(',', $aFrom) . "\n";
      if ($aTo)
        $sHeaders .= 'To: '   . implode(',', $aTo)   . "\n";
      if ($aCc)
        $sHeaders .= 'Cc: '   . implode(',', $aCc)   . "\n";
      if ($aBcc)
        $sHeaders .= 'Bcc: '  . implode(',', $aBcc)  . "\n";
      
      // Build messages:
      
      $sBoundary = str_replace('-', '', BnetDocs::fGenerateUUIDv4());
      
      $sHeaders .= "Content-Type: multipart/alternative;"
               .  "boundary=" . $sBoundary . "\n";
      
      $sMessage = '';
      
      foreach ($this->aMessageFlavors as $oMessage) {
        $sMessage .= "--" . $sBoundary . "\n";
        $sMessage .= "Content-Type: " . $oMessage->fGetContentType() . "\n\n";
        $sMessage .= $oMessage->fGetBody() . "\n\n";
      }
      
      $sMessage .= "--" . $sBoundary . "--";
      
      return mail('', $this->fGetSubject(), $sMessage, $sHeaders);
    }
    
    public function fSetSubject($sSubject) {
      if (!is_string($sSubject))
        throw new Exception('Subject is not of type string');
      if (empty($sSubject))
        throw new RecoverableException('Subject is an empty string');
      $this->sSubject = $sSubject;
      return true;
    }
    
    public static function fSendPasswordReset(User &$oUser) {
      $sEmail = $oUser->fGetEmail();
      $sUsername = $oUser->fGetUsername();
      $sDisplayName = $oUser->fGetDisplayName();
      if (empty($sDisplayName)) $sDisplayName = $sUsername;
      $sVerifiedId = $oUser->fGetVerifiedId();
      
      $sVerifiedURL = BnetDocs::fGetCurrentFullURL('/user/password_reset?id=' . rawurlencode($sVerifiedId));
      
      $oEmail = new self();
      
      $oEmail->fAddRecipient(self::$oBNETDocsRecipient);
      $oEmail->fAddRecipient(new EmailRecipient(
        $sEmail, EmailRecipient::TYPE_TO, $sDisplayName, false
      ));
      
      $oEmail->fSetSubject('Reset your BNETDocs account password');
      
      // Text version:
      $oEmail->fAddMessage(new EmailMessage(
        'text/plain;charset=utf-8',
        $sDisplayName . ",\n\n"
        . "You can reset the password to your account *" . $sUsername . "* on *BNETDocs* by copying and pasting the link below into your web browser.\n\n"
        . $sVerifiedURL . "\n\n"
        . "If you did not request this, it means someone else tried to reset your password. Simply ignore this email if you did not request this.\n\n\n"
        . "Thanks,\n\n"
        . "BNETDocs"
      ));
      
      // HTML version:
      $oEmail->fAddMessage(new EmailMessage(
        'text/html;charset=utf-8',
        $sDisplayName . ",<br><br>\n\n"
        . "You can reset the password to your account <b>" . $sUsername . "</b> on <b>BNETDocs</b> by clicking the link below.<br><br>\n\n"
        . "<a href=\"" . $sVerifiedURL . "\">" . $sVerifiedURL . "</a><br><br>\n\n"
        . "If you did not request this, it means someone else tried to reset your password. Simply ignore this email if you did not request this.<br><br><br>\n\n\n"
        . "Thanks,<br><br>\n\n"
        . "BNETDocs"
      ));
      
      return $oEmail->fSend();
    }
    
    public static function fSendWelcome(User &$oUser) {
      $sEmail = $oUser->fGetEmail();
      $sUsername = $oUser->fGetUsername();
      $sDisplayName = $oUser->fGetDisplayName();
      if (empty($sDisplayName)) $sDisplayName = $sUsername;
      $sVerifiedId = $oUser->fGetVerifiedId();
      
      $sVerifiedURL = BnetDocs::fGetCurrentFullURL('/user/verify?id=' . rawurlencode($sVerifiedId));
      
      $oEmail = new self();
      
      $oEmail->fAddRecipient(self::$oBNETDocsRecipient);
      $oEmail->fAddRecipient(new EmailRecipient(
        $sEmail, EmailRecipient::TYPE_TO, $sDisplayName, false
      ));
      
      $oEmail->fSetSubject('Welcome to BNETDocs!');
      
      // Text version:
      $oEmail->fAddMessage(new EmailMessage(
        'text/plain;charset=utf-8',
        "Hi " . $sDisplayName . ",\n\n"
        . "Your account " . $sUsername . " has been successfully registered on BNETDocs!\n\n"
        . "To begin using your account, you will need to activate it by verifying through this email that you created the account. Copy and paste the following link into your web browser to do that.\n\n"
        . $sVerifiedURL . "\n\n"
        . "If you did not create this account, simply ignore this email.\n\n\n"
        . "Thanks,\n\n"
        . "BNETDocs"
      ));
      
      // HTML version:
      $oEmail->fAddMessage(new EmailMessage(
        'text/html;charset=utf-8',
        "Hi " . $sDisplayName . ",<br><br>\n\n"
        . "Your account <b>" . $sUsername . "</b> has been successfully registered on <b>BNETDocs</b>!<br><br>\n\n"
        . "To begin using your account, you will need to activate it by verifying through this email that you created the account. Click the following link to do that.<br><br>\n\n"
        . "<a href=\"" . $sVerifiedURL . "\">" . $sVerifiedURL . "</a><br><br>\n\n"
        . "If you did not create this account, simply ignore this email.<br><br><br>\n\n\n"
        . "Thanks,<br><br>\n\n"
        . "BNETDocs"
      ));
      
      return $oEmail->fSend();
    }
    
  }
  