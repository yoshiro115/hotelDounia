<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Membre;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MembreCrudController extends AbstractCrudController
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function getEntityFqcn(): string
    {
        return Membre::class;
    } // ° Penser à faire date_enregistrement pour CommandeCrudController


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom'),
            TextField::new('prenom', "Prénom"),
            TextField::new('pseudo'),
            TextField::new('email'),
            ChoiceField::new('civilite')->setChoices(['Homme' => 'h', "Femme" => "f"]),
            TextField::new('password', "Mot de passe")->setFormType(PasswordType::class)->onlyWhenCreating(),
            CollectionField::new('roles')->setTemplatePath('admin/field/roles.html.twig'),
            DateTimeField::new('date_enregistrement')->setFormat('d/M/Y à H:m:s')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        //$entityInstance correspond au $user
        if (!$entityInstance->getId()) {
            $entityInstance->setPassword(
                $this->hasher->hashPassword(
                    $entityInstance,
                    $entityInstance->getPassword()
                )
            );
        }
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function createEntity(string $entityFqcn)
    {
        $membre = new $entityFqcn;
        $membre->setDateEnregistrement(new DateTime);
        return $membre;
    }
}
