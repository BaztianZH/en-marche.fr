<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentArea;
use AppBundle\Entity\Referent;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\ReferentTagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/le-mouvement/nos-referents")
 */
class ReferentNominationController extends Controller
{
    /**
     * @Route("", name="our_referents_homepage")
     * @Method("GET")
     */
    public function indexAction(
        AdherentRepository $adherentRepository,
        ReferentTagRepository $referentTagRepository
    ): Response {
        $doctrine = $this->getDoctrine();
        $referentAreasRepository = $doctrine->getRepository(ReferentArea::class);

        return $this->render('referent/nomination/homepage.html.twig', [
            'referents' => $adherentRepository->findReferentsByStatusOrderedByAreaLabel(),
            'groupedZones' => $referentAreasRepository->findAllGrouped(),
        ]);
    }

    /**
     * @Route("/{slug}", name="our_referents_referent")
     * @Entity("referent", class="AppBundle\Entity\Adherent", expr="repository.findReferentBySlug(slug)")
     * @Method("GET")
     */
    public function candidateAction(Adherent $referent): Response
    {
        return $this->render('referent/nomination/referent.html.twig', [
            'referent' => $referent,
        ]);
    }
}
