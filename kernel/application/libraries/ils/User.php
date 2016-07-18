<?php

/**
 * Represents an ils user
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class User {
    
    /**
     * @var String
     */
    protected   $username   = '';
    
    /**
     * @var String
     */
    protected   $firstname  = '';
    
    /**
     * @var String
     */
    protected   $lastname   = '';
    
    /**
     * @var String
     */
    protected   $email      = '';
    
    /**
     * @var String
     */
    protected   $address    = '';
    
    /**
     * @var String
     */
    protected   $expires    = '';
    
    /**
     * @var String
     */
    protected   $status     = '';

    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }
    
    public function getUsername() {
        return $this->username;
    }

    public function setFirstname($firstname) {
        $this->firstname = $firstname;
        return $this;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function setLastname($lastname) {
        $this->lastname = $lastname;
        return $this;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setExpires($expires) {
        $this->expires = $expires;
        return $this;
    }

    public function getExpires() {
        return $this->expires;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }
    
    public function toArray()
    {
        return get_object_vars($this);
    }
}
