<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;

class WhoAmiDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $this->addWhoamiPath($openApi);

        return $openApi;
    }

    private function addWhoamiPath(OpenApi $openApi): void
    {
        $pathItem = new Model\PathItem(
            ref: 'WhoAmI',
            get: new Model\Operation(
                operationId: 'whoami',
                tags: ['User'],
                responses: [
                    '200' => [
                        'description' => 'User informations',
                        'content' => [
                            'application/json' => [],
                        ],
                    ],
                    '401' => [
                        'description' => 'Unauthorized',
                    ],
                    '400' => [
                        'description' => 'Any error',
                    ],
                ],
                summary: 'Get User informations',
            ),
        );

        $openApi->getPaths()->addPath('/api/whoami', $pathItem);
    }
}
