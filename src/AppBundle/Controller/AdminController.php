<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function indexAction(EntityManagerInterface $em)
    {
        // $em = $this->get('doctrine')->getManager();
        $products = $em->getRepository('AppBundle:Product')
            ->findAll();

        $users = $em->getRepository('AppBundle:User')
            ->findAll();

        return $this->render('admin/index.html.twig', [
            'products' => $products,
            'users' => $users,
        ]);
    }
}
