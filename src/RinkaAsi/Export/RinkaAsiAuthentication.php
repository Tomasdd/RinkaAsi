<?php

class RinkaAsiAuthentication {

    /**
     * DO NOT CAHNGE - the same additional salt is on remote server, so authentication would not pass
     */
    const additionalSalt = 'KeG*&6sa5AS^&_@Fm';
    protected $username;
    protected $password;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

     /**
     * Example:
     * <authentication>
     *      <username>username</username>
     *      <salt>e5ad3928604608c15f4a880aa6618521</salt>
     *      <passwordhash>482fb37fad59cecf35dd7cd3c439c12c</passwordhash>
     * </authentication>
     *
     * @param DOMDocument $DomDocument
     * @return DOMNode
     */
    public function getDomNode(DOMDocument $DomDocument) {
        $Node = $DomDocument->createElement('authentication');

        $salt = $this->getSalt();
        $Node->appendChild($DomDocument->createElement('username', htmlspecialchars($this->getUsername())));
        $Node->appendChild($DomDocument->createElement('salt', htmlspecialchars($salt)));
        $Node->appendChild($DomDocument->createElement('passwordhash', htmlspecialchars($this->getPasswordHash($salt))));

        return $Node;
    }

    public function getCredentials() {
        $salt = $this->getSalt();
        return array(
            'username'     => $this->getUsername(),
            'passwordhash' => $this->getPasswordHash($salt),
            'salt'         => $salt,
        );
    }

    protected function getUsername() {
        return $this->username;
    }
    protected function getSalt() {
        return md5('123');
    }
    protected function getPasswordHash($salt) {
        return md5($salt . self::additionalSalt . md5($this->password));
    }
}
