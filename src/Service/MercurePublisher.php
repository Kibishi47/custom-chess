<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher
{
    public function __construct(
        private HubInterface $hub,
        private RequestStack $requestStack
    ) {}

    public function publish(string $path, array $data, bool $private = false): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request ? $request->getSchemeAndHttpHost() : $_ENV['APP_URL'];
        $topic = $baseUrl . $path;

        $update = new Update($topic, json_encode($data), $private);
        $this->hub->publish($update);
    }
}
