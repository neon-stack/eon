<?php

declare(strict_types=1);

namespace App\Controller\WebDAV;

use App\Factory\Exception\Server501NotImplementedExceptionFactory;
use App\Helper\Regex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MoveElementController extends AbstractController
{
    public function __construct(
        private Server501NotImplementedExceptionFactory $server501NotImplementedExceptionFactory
    ) {
    }

    #[Route(
        '/{id}',
        name: 'move-element',
        requirements: [
            'id' => Regex::UUID_V4_CONTROLLER,
        ],
        methods: ['MOVE']
    )]
    public function moveElement(string $id, Request $request): Response
    {
        throw $this->server501NotImplementedExceptionFactory->createFromTemplate();
    }
}
