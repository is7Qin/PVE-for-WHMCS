<?php

/**
 * Ipv6_SubnetIterator 
 * An object that implements a subnet iterator
 * 
 * @uses Iterator
 * @package Ipv6
 * @version $id$
 * @copyright 2023 Your Name
 * @license MIT
 */
class Ipv6_SubnetIterator implements Iterator
{
  private $position = 0;
  private $low_bin;
  private $hi_bin;

  public function __construct(Ipv6_Subnet $subnet) {
    $this->low_bin = $subnet->ipv6_to_binary($subnet->getFirstHostAddr());
    $this->hi_bin = $subnet->ipv6_to_binary($subnet->getLastHostAddr());
  }

  function rewind() {
    $this->position = 0;
  }

  function current() {
    return $this->binary_to_ipv6($this->low_bin + $this->position);
  }

  function key() {
    return $this->position;
  }

  function next() {
    ++$this->position;
  }

  function valid() {
    return (($this->low_bin + $this->position) <= $this->hi_bin);
  }

  private function binary_to_ipv6($binary)
  {
    return inet_ntop($binary);
  }
}
