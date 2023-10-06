<?php

namespace App\Controller\Admin;

use App\Controller\OrderController;
use App\Entity\Order;
use App\Repository\OrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class OrderCrudController extends AbstractCrudController
{
    private $admindUrlGenerator;
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository, AdminUrlGenerator $admindUrlGenerator)
    {
        $this->orderRepository = $orderRepository;
        $this->admindUrlGenerator = $admindUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation', 'préparation en cours', 'fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery', 'livraison en cours', 'fas fa-truck')->linkToCrudAction('updateDelivery');
        return $actions
            ->add('detail', $updatePreparation)
            ->add('detail', $updateDelivery)
            ->add('index','detail');
    }
    public function updatePreparation(AdminContext $content)
    {
        $order = $content->getEntity()->getInstance();
        $order->setState(2);
        $this->orderRepository->onFlush(true);

        $this->addFlash('notice', "<span style='color:green;'><strong>La commande " . $order->getReference() . " est bien <u>en cour de préparation</u>.</strong></span");

        $url = $this->admindUrlGenerator->setController(OrderController::class)
            ->setAction('index')
            ->generateUrl();
        
        return $this->redirect($url);
    }

    public function updateDelivery(AdminContext $content)
    {
        $order = $content->getEntity()->getInstance();
        $order->setState(3);
        $this->orderRepository->onFlush(true);

        $this->addFlash('notice', "<span style='color:orange;'><strong>La commande " . $order->getReference() . " est bien <u>en cour de livraison</u>.</strong></span");
 
        $url = $this->admindUrlGenerator->setController(OrderController::class)
            ->setAction('index')
            ->generateUrl();
        
        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateField::new('createAt', 'Passé le'),
            TextareaField::new('user.fullname', 'Utilisateur'),
            TextEditorField::new('delivery', 'Adresse de livraison')
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                return $value;
            }),
            MoneyField::new('total', 'Total produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice', 'Frais de port')->setCurrency('EUR'),  
            ChoiceField::new('state')->setChoices([
                'Non payé' => 0,
                'Payé' => 1,
                'Préparation en cours' => 2,
                'livraison en cours' => 3
            ]),
            ArrayField::new('OrderDetails', 'Produits achetés')->hideOnIndex()
        ];
    }
    
}
