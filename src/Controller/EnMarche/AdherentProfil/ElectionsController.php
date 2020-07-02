<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/elections", name="app_adherent_profil_elections", methods={"GET"})
 */
class ElectionsController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/elections.html.twig');
    }
}
