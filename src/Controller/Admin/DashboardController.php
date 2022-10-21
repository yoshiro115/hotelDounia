<?php

namespace App\Controller\Admin;

use App\Entity\Membre;
use App\Entity\Slider;
use App\Entity\Chambre;
use App\Entity\Commande;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\ChambreCrudController;
use App\Entity\Avis;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    private $routeBuilder; // ° retirer

    public function __construct(AdminUrlGenerator $routebuilder)
    {
        $this->routeBuilder = $routebuilder;
    }
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        // permet de rediriger vers les chambres pour ne pas afficher la page d'accueil par défaut (voir doc easyadmin)
        return $this->redirect($this->routeBuilder->setController(ChambreCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('BACKOFFICE Hotel');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToRoute('Retour Accueil', 'fa fa-arrow-left', 'accueil'),
            MenuItem::section("Gestion du Site"),
            MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home'),
            MenuItem::linkToCrud('Administrateurs', 'fas fa-user', Membre::class),
            MenuItem::linkToCrud('Avis', 'fas fa-pen', Avis::class),
            MenuItem::linkToCrud('Slider', 'fas fa-image', Slider::class),
            MenuItem::section('Commandes'),
            MenuItem::section('Hotel'),
            MenuItem::linkToCrud('Chambre', 'fas fa-bed', Chambre::class),
            MenuItem::linkToCrud('commandes', 'fas fa-cash-register', Commande::class),

        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
