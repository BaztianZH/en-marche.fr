<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\TerritorialCouncil\OfficialReport;
use App\Form\OfficialReportType;
use App\Repository\TerritorialCouncil\OfficialReportRepository;
use App\TerritorialCouncil\Convocation\OfficialReportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/instances/proces-verbaux", name="app_instances_official_report_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class OfficialReportManagerController extends AbstractController
{
    /**
     * @Route("", name="_list", methods={"GET"})
     */
    public function listAction(Request $request, OfficialReportRepository $repository): Response
    {
        return $this->render(
            'referent/territorial_council/official_report_list.html.twig',
            ['paginator' => $repository->getPaginator($request->query->getInt('page', 1))]
        );
    }

    /**
     * @Route("/creer", name="_create", methods={"GET", "POST"})
     */
    public function createAction(Request $request, OfficialReportManager $manager): Response
    {
        $form = $this
            ->createForm(OfficialReportType::class, $object = new OfficialReport($this->getUser()))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->handleRequest($object);

            $this->addFlash('info', 'Le procès-verbal a été ajouté avec succès.');

            return $this->redirectToRoute('app_instances_official_report_referent_list');
        }

        return $this->render(
            'referent/territorial_council/official_report_create.html.twig',
            ['form' => $form->createView()]
        );
    }

//    /**
//     * @Route(
//     *     "/proces-verbaux/telecharger/{type}/{path}",
//     *     requirements={"type": App\Sitemap\SitemapFactory::ALL_TYPES, "path": ".pdf"},
//     *     name="official_reports_file",
//     *     methods={"GET"}
//     * )
//     */
//    public function fileAction($type, $path)
//    {
//        /** @var Adherent $adherent */
//        $adherent = $this->getUser();
//
//        if (($type && $adherent->isTerritorialCouncilMember())
//            || ($adherent->isPoliticalCommitteeMember() && $type)) {
//            'Vous n\'avez pas le droit d\'accéder à cette page ';
//        }
//
//        try {
//            $document = $this->get('app.document_manager')->readDocument($type, $path);
//        } catch (FileNotFoundException $e) {
//            throw $this->createNotFoundException('Document not found', $e);
//        }
//
//        $response = new Response($document['content']);
//        $response->headers->set('Content-Type', $document['mimetype']);
//
//        return $response;
//    }
}
