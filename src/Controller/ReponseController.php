<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reclamation;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\SendMailService;

#[Route('/reponse')]
class ReponseController extends AbstractController
{
    
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    #[Route('/', name: 'app_reponse_index', methods: ['GET'])]
    public function index(ReponseRepository $reponseRepository, EntityManagerInterface $entityManager, Request $request , PaginatorInterface $paginator): Response
    {
        // Assuming you have a method to retrieve the reclamation object, replace '1' with the actual ID
        $reclamationId = 1;
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
    


        $query = $reponseRepository->findAll();
        // Handle search
        $searchQuery = $request->query->get('q');
        if (!empty($searchQuery)) {
             $query = $reponseRepository->findByExampleField($searchQuery);
        }
        $reponse = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Current page number
            3 // Number of items per page
        );
        if ($request->isXmlHttpRequest()) {
                $paginationHtml = $this->renderView('reponse/_paginator.html.twig', ['reponses' => $reponse]);
                $contentHtml = $this->renderView('reponse/reponse_list.html.twig', ['reponses' => $reponse]);
               // Log to verify if the controller enters this condition
                       $this->logger->info('Request is AJAX');
               return new JsonResponse([
                    'content' => $contentHtml,
                    'pagination' => $paginationHtml
                    ]);
       } else {
        // Log to verify if the controller enters this condition
       $this->logger->info('Request is not AJAX');
        }


        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponse,
            'reclamation' => $reclamation,
        ]);
    }
    


    #[Route('/new/{idrec}', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $idrec, SendMailService  $mail ): Response
    {
        // Fetch the corresponding Reclamation entity based on the idrec parameter
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($idrec);

        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found');
        }

        $reponse = new Reponse();
        $reponse->setIdrec($reclamation);

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();

            // Update the etat attribute of the associated Reclamation entity to "completed"
            $reclamation->setEtat('completed');
            $entityManager->flush();
            // send mail function 
            $mail->send(
                'ines.ketata@esprit.tn ',
                'iketata1@gmail.com',
                'reclamtion traitée',
                'approve',
                [
                    'reponse' => $reponse,
                ]
            );

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }
}
