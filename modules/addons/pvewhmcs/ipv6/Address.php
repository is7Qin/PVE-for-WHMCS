<?php

class Ipv6_Address
{
  private $ip_long;
  const ERROR_ADDR_FORMAT = 'IPv6 address string format error';

  /**
   * fromString
   * Creates Ipv6_Address object from a standard IPv6 address string
   *
   * @param string $data
   * @static
   * @access public
   * @return Ipv6_Address
   */
  static function fromString($data) {
    if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      return new self(inet_pton($data));
    }
    throw new Exception(self::ERROR_ADDR_FORMAT);
  }

  /**
   * fromBinary
   * Creates Ipv6_Address object from a binary address
   *
   * @param string $data
   * @static
   * @access public
   * @return Ipv6_Address
   */
  static function fromBinary($data) {
    return new self($data);
  }

  /**
   * toString
   * Returns value as standard IPv6 address string
   *
   * @access public
   * @return string
   */
  public function toString() {
    return inet_ntop($this->ip_long);
  }

  /**
   * toBinary
   * Returns binary representation of IPv6 address
   *
   * @access public
   * @return string
   */
  public function toBinary() {
    return $this->ip_long;
  }

  /**
   * __toString
   * Magic method returns standard IPv6 address string
   *
   * @access public
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

  /**
   * __construct
   * Private constructor
   *
   * @param string $binary
   * @access private
   * @return void
   */
  private function __construct($binary) {
    $this->ip_long = $binary;
  }

}
