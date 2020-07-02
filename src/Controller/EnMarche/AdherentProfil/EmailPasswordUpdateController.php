<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/email-et-mot-de-passe", name="app_adherent_profil_email_password_update", methods={"GET"})
 */
class EmailPasswordUpdateController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/email-and-password.html.twig');
    }
}
