<?php

namespace App\Controller;

use App\Helper\Regex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syndesi\CypherEntityManager\Type\EntityManager as CypherEntityManager;

class PatchUuidController extends AbstractController
{
    public function __construct(private CypherEntityManager $cypherEntityManager)
    {
    }

    #[Route(
        '/{uuid}',
        name: 'patchUuid',
        requirements: [
            'uuid' => Regex::UUID_V4,
        ],
        methods: ['PATCH']
    )]
    public function patchUuid(string $uuid): Response
    {
        return new Response('it worked :D');
    }
}
