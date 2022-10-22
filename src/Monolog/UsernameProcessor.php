<?php

namespace App\Monolog;

use Monolog\Processor\ProcessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UsernameProcessor implements ProcessorInterface {

    private ?string $username = null;

    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @inheritDoc
     */
    public function __invoke(array $record): array {
        $record['extra']['username'] = $this->getUsername();

        return $record;
    }

    private function getUsername(): ?string {
        if($this->username === null) {
            $token = $this->tokenStorage->getToken();

            if($token !== null) {
                $this->username = $token->getUserIdentifier();
            }
        }

        return $this->username;
    }
}