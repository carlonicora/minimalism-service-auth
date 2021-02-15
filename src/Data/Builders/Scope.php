<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Builders;

use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use Exception;

class Scope extends AbstractResourceBuilder
{
    /** @var string  */
    public string $type = 'scope';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $this->encrypter->encryptId($data['scopeId']);

        $this->response->attributes->add('name', $data['name']);
    }
}