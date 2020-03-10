<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Assessor\AssessorRole\AssessorAssociationManager;
use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Assessor\Filter\AssociationVotePlaceFilter;
use AppBundle\Election\ElectionManager;
use AppBundle\Entity\AssessorRoleAssociation;
use AppBundle\Entity\Election;
use AppBundle\Entity\VotePlace;
use AppBundle\Exporter\AssessorsExporter;
use AppBundle\Exporter\VoteResultsExporter;
use AppBundle\Form\AssessorVotePlaceListType;
use AppBundle\Form\CreateVotePlaceType;
use AppBundle\Repository\Election\VotePlaceResultRepository;
use AppBundle\Repository\ElectionRepository;
use AppBundle\Repository\VotePlaceRepository;
use AppBundle\Security\Voter\ManageVotePlaceVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractAssessorSpaceController extends Controller
{
    protected const PAGE_LIMIT = 10;

    protected $votePlaceRepository;
    protected $voteResultRepository;

    public function __construct(
        VotePlaceRepository $votePlaceRepository,
        VotePlaceResultRepository $voteResultRepository
    ) {
        $this->votePlaceRepository = $votePlaceRepository;
        $this->voteResultRepository = $voteResultRepository;
    }

    /**
     * @Route("/bureaux-de-vote", name="_attribution_form", methods={"GET", "POST"})
     */
    public function votePlaceAttributionAction(
        Request $request,
        AssessorAssociationManager $manager,
        ElectionManager $electionManager
    ): Response {
        $filterForm = $this->createVotePlaceListFilterForm($filter = $this->createVotePlaceListFilter())->handleRequest($request);

        if ($filterForm->isSubmitted() && !$filterForm->isValid()) {
            $filter = $this->createVotePlaceListFilter();
        }

        $paginator = $this->getVotePlacesPaginator($request->query->getInt('page', 1), $filter);

        $form = $this
            ->createForm(AssessorVotePlaceListType::class, $manager->getAssociationValueObjectsFromVotePlaces(
                iterator_to_array($paginator)
            ))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->handleUpdate($form->getData());

            $this->addFlash('info', 'Les modifications ont bien été sauvegardées');

            return $this->redirectToRoute(
                sprintf('app_assessors_%s_attribution_form', $this->getSpaceType()),
                $this->getRouteParams($request)
            );
        }

        return $this->renderTemplate('assessor_attribution/index.html.twig', [
            'current_election_round' => $electionManager->getClosestElectionRound(),
            'vote_places' => $paginator,
            'form' => $form->createView(),
            'filter_form' => $filterForm->createView(),
            'route_params' => $this->getRouteParams($request),
        ]);
    }

    /**
     * @Route("/export.{_format}", name="_export", methods={"GET"}, defaults={"_format": "xls"}, requirements={"_format": "csv|xls"})
     */
    public function exportAssessorsAction(string $_format, AssessorsExporter $exporter): Response
    {
        return $exporter->getResponse($_format, $this->getAssessorRequestExportFilter());
    }

    /**
     * @Route("/bureaux-de-vote/{id}/desactiver", name="_disable_vote_place", methods={"GET"}, defaults={"enabled": false})
     * @Route("/bureaux-de-vote/{id}/activer", name="_enable_vote_place", methods={"GET"}, defaults={"enabled": true})
     */
    public function toggleVotePlaceStatusAction(
        bool $enabled,
        VotePlace $votePlace,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted(ManageVotePlaceVoter::MANAGE_VOTE_PLACE, $votePlace);

        if (false === $enabled && $entityManager->getRepository(AssessorRoleAssociation::class)->findOneBy(['votePlace' => $votePlace])) {
            $this->addFlash('error', 'Vous ne pouvez pas désactiver un bureau de vote attribué. Veuillez d’abord y supprimer le mail de l’assesseur');
        } else {
            $votePlace->setEnabled($enabled);
            $entityManager->flush();

            $this->addFlash('info', sprintf('Le bureau de vote "%s" a bien été '.($enabled ? 'activé' : 'désactivé'), $votePlace->getName()));
        }

        return $this->redirectToRoute(
            sprintf('app_assessors_%s_attribution_form', $this->getSpaceType()),
            $this->getRouteParams($request)
        );
    }

    /**
     * @Route("/bureaux-de-vote/ajouter", name="_create_vote_place", methods={"GET", "POST"})
     */
    public function createVotePlaceAction(Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this
            ->createForm(CreateVotePlaceType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('info', 'Le nouveau bureau de vote a bien été ajouté');

            return $this->redirectToRoute(sprintf('app_assessors_%s_attribution_form', $this->getSpaceType()));
        }

        return $this->renderTemplate('assessor_attribution/create_vote_place.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resultats/export.{_format}", name="_results_export", methods={"GET"}, defaults={"_format": "xls"}, requirements={"_format": "csv|xls"})
     */
    public function exportResultsAction(
        string $_format,
        VoteResultsExporter $exporter,
        ElectionRepository $electionRepository
    ): Response {
        $election = $electionRepository->findComingNextElection();

        return $exporter->getResponse($_format, $this->getVoteResultsExportQuery($election));
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('assessor_attribution/_base_%s_space.html.twig', $spaceType = $this->getSpaceType()),
                'space_type' => $spaceType,
            ]
        ));
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getAssessorRequestExportFilter(): AssessorRequestExportFilter;

    abstract protected function createVotePlaceListFilterForm(AssociationVotePlaceFilter $filter): FormInterface;

    abstract protected function createVotePlaceListFilter(): AssociationVotePlaceFilter;

    abstract protected function getVoteResultsExportQuery(Election $election): Query;

    /**
     * @return VotePlace[]|PaginatorInterface
     */
    private function getVotePlacesPaginator(int $page, AssociationVotePlaceFilter $filter): PaginatorInterface
    {
        return $this->votePlaceRepository->findAllForFilter($filter, $page, self::PAGE_LIMIT);
    }

    protected function getRouteParams(Request $request): array
    {
        $params = [];

        if ($request->query->has('f')) {
            $params['f'] = (array) $request->query->get('f');
        }

        if ($request->query->has('page')) {
            $params['page'] = $request->query->getInt('page');
        }

        return $params;
    }
}
