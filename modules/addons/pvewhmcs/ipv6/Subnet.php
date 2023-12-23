<?php

class Ipv6_Subnet implements Countable
{
  const ERROR_NETWORK_FORMAT = 'IPv6 format incorrect';
  const ERROR_CIDR_FORMAT = 'Invalid CIDR format';
  const ERROR_SUBNET_FORMAT = 'Invalid Subnet format';

  private $network;
  private $subnet;

  public function __construct($network = null, $subnet = null)
  {
    if (is_string($network)) $network = $this->ipv6_to_binary($network);
    if (is_string($subnet)) $subnet = $this->ipv6_to_binary($subnet);

    if ($network && !$subnet) {
      $this->setFromString($this->binary_to_ipv6($network));
    } elseif ($network && $subnet) {
      $this->setNetwork($network)->setSubnet($subnet);
    }
  }

  public static function fromString($data)
  {
    return new Ipv6_Subnet($data);
  }

  public static function CIDRtoIP($cidr)
  {
    if (!($cidr >= 0 and $cidr <= 128))
      throw new Exception(self::ERROR_CIDR_FORMAT);

    return $this->binary_to_ipv6(str_pad(str_pad('', $cidr, '1'), 128, '0'));
  }

  public static function ContainsAddress($subnet, $ip)
  {
    if (is_string($subnet)) $subnet = Ipv6_Subnet::fromString($subnet);
    if (is_string($ip)) $ip = Ipv6_Address::fromString($ip);

    if (!$subnet instanceof Ipv6_Subnet) throw new Exception(self::ERROR_SUBNET_FORMAT);
    if (!$ip instanceof Ipv6_Address) throw new Exception(Ipv6_Address::ERROR_ADDR_FORMAT);

    $subnet_binary = $subnet->getSubnet();
    $ip_binary = $ip->toBinary();

    return (strncmp($ip_binary, $subnet_binary, strlen($subnet_binary)) === 0);
  }

  public function setFromString($data)
  {
    if (!filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
      throw new Exception(self::ERROR_NETWORK_FORMAT);

    list($network, $subnet) = explode('/', $data, 2);
    $this->setNetwork($network)->setSubnet(self::CIDRtoIP($subnet));

    return $this;
  }

  public function contains($ip)
  {
    return self::ContainsAddress($this, $ip);
  }

  public function setNetwork($network)
  {
    $this->network = $this->ipv6_to_binary($network);
    return $this;
  }

  public function setSubnet($subnet)
  {
    $subnet = $this->ipv6_to_binary($subnet);

    if (!preg_match('/^1*0*$/',$subnet))
      throw new Exception(self::ERROR_SUBNET_FORMAT);

    $this->subnet = $subnet;
    return $this;
  }

  public function getSubnet()
  {
    return $this->binary_to_ipv6($this->subnet);
  }

  public function getSubnetCidr()
  {
    return strlen(rtrim($this->subnet,'0'));
  }

  public function getNetwork()
  {
    $network_binary = $this->binary_to_ipv6($this->network);
    $network_binary = (str_pad(substr($network_binary, 0, $this->getSubnetCidr()), 128, '0'));
    return $this->binary_to_ipv6($network_binary);
  }

  public function getTotalHosts()
  {
    return bindec(str_pad('', (128 - $this->getSubnetCidr()), 1));
  }

  public function getIterator()
  {
    if (!class_exists('Ipv6_SubnetIterator'))
      require_once(dirname(__FILE__).'/SubnetIterator.php');

    return new Ipv6_SubnetIterator($this);
  }

  private function ipv6_to_binary($ipv6)
  {
    return inet_pton($ipv6);
  }

  private function binary_to_ipv6($binary)
  {
    return inet_ntop($binary);
  }

  public function __toString()
  {
    return sprintf(
      '%s/%s',
      $this->getNetwork(),
      $this->getSubnetCidr()
    );
  }

  public function count()
  {
    return $this->getTotalHosts();
  }
}
