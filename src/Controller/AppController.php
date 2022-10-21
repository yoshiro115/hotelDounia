<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Entity\Commande;
use App\Form\ContactType;
use App\Form\CommandeType;
use Doctrine\ORM\EntityManager;
use App\Repository\AvisRepository;
use App\Repository\SliderRepository;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    // $        Accueil
    // $        Newsletter
    #[Route('/', name: 'accueil')] // ! anciennement app_main
    public function index(SliderRepository $repo): Response
    {
        if ($_POST) {
            if (!empty($_POST['mail'])) {
                $this->addFlash('success', "Inscription à la newsletter réussie");
            } else {
                $this->addFlash('info', "Pour vous inscrire à la newsletter merci d'entrer votre mail");
            }
        }
        $slider = $repo->findAll();
        return $this->render('app/index.html.twig', [
            'photos' => $slider,
        ]);
    }

    // $        Chambres
    #[Route('/chambres', name: 'chambres')]
    public function chambres(ChambreRepository $repo): Response
    {
        $chambres = $repo->findAll();
        return $this->render('app/chambres.html.twig', [
            'chambres' => $chambres
        ]);
    }
    #[Route('/show/chambre/{id}', name: 'show_chambre')]
    public function chambre(ChambreRepository $repo, $id, Request $globals, EntityManagerInterface $manager): Response
    {
        $chambre = $repo->find($id);

        $commande = new Commande;
        
        $form = $this->createForm(CommandeType::class, $commande );

        $form->handleRequest($globals);

        if($form->isSubmitted() && $form->isValid())
        { 
            $depart = $commande->getDateArrivee();
            $fin = $commande->getDateDepart();
            $interval = $depart->diff($fin);
            $days = $interval->days;
            $commande->setDateEnregistrement(new \DateTime);
            $prix = $chambre->getPrixJournalier();
            $prix = $prix * $days;
            $commande->setPrixTotal($prix);
            $commande->setChambre($chambre);
            $manager->persist($commande);
            $manager->flush();
            $this->addFlash('warning', "Réservation exécutée avec succès !");
            return $this->redirectToRoute('accueil');
        }
        return $this->renderForm('app/chambre_reservation.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    // $        Cartes/Menus
    #[Route('/cartes', name: 'cartes')]
    public function cartes(): Response
    {
        return $this->render('app/cartes.html.twig');
    }

    // $        Spas
    #[Route('/spas', name: 'spas')]
    public function spas(ChambreRepository $repo)
    {
        $spas = $repo->findOneBy(["titre" => "spas"]);
        return $this->render('app/spas.html.twig', [
            "chambre" => $spas,
        ]);
    }

    // $        Avis
    #[Route('/avis/filtre', name: 'avis_filtre')]
    #[Route('/avis', name: 'avis')]
    public function avis(EntityManagerInterface $manager, Request $globals, AvisRepository $repo, $categorie = null)
    {

        if ($globals->request->get('categorie') != null) {
            $categorie = $globals->request->get('categorie');
        }
        $avisFiltre = $repo->findBy(["categorie" => $categorie]);

        $avis = $repo->findAll();

        $comment = new Avis;
        $form = $this->createForm(AvisType::class, $comment);
        $form->handleRequest($globals);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setDateEnregistrement(new \DateTime);
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute("avis", [
                'avis' => $avis,
                'form' => $form,
            ]);
        }

        return $this->renderForm('app/avis.html.twig', [
            'avis' => $avis,
            'form' => $form,
            'categorie' => $categorie,
            'filtre' => $avisFiltre
        ]);
    }

    // $        Actualités
    #[Route('/actu', name: 'actu')]
    public function actu()
    {
        $rss = simplexml_load_file('https://www.lhotellerie-restauration.fr/rss/actu_rss.xml?xtor=RSS-1');
        return $this->render('app/actu.html.twig', [
            'rssItems' => $rss->channel->item,
        ]);
    }

    // $        Contact
    #[Route('/contact', name:'contact')]
    public function contact(Request $globals): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($globals);
        if($form->isSubmitted() && $form->isValid())
        { 
            $this->addFlash('success', "Envoyé");
            return $this->redirectToRoute('accueil');
        }
        
        return $this->renderForm('app/contact.html.twig', [
            'form' => $form
        ]);
        
    }

    // $        About
    #[Route('/about', name:'about')]
    public function about(Request $globals): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($globals);
        if($form->isSubmitted() && $form->isValid())
        { 
            $this->addFlash('success', "Envoyé");
            return $this->redirectToRoute('accueil');
        }
        return $this->renderForm('app/about.html.twig', [
            'form' => $form
        ]);
    }

    // $        Mentions légales
    #[Route('/mentions-legales', name:'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('app/mentions_legales.html.twig');
    }

    // $        cgv
    #[Route('/cgv', name:'cgv')]
    public function cgv(): Response
    {
        return $this->render('app/cgv.html.twig');
    }

    
}
