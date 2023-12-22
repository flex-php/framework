<?php

namespace Flex\View\Variables;

class VariablesBag
{
  public function __construct(protected array $data = [])
  {
  }

  public function get(string $key): mixed
  {
    return $this->data[$key] ?? null;
  }

  public function set(string $key, mixed $value): void
  {
    $this->data[$key] = $value;
  }

  public function all(): array
  {
    return $this->data;
  }

  public function merge(array $data): void
  {
    $this->data = array_merge($this->data, $data);
  }
}