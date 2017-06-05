<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Product;
use AppBundle\Form\ProductType;

class ProductController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(EntityManagerInterface $em)
    {
        $query = $em->getRepository('AppBundle:Product')
            ->createQueryBuilder('p')
            ->orderBy('p.price', 'ASC')
            ->getQuery();

        $products = $query->getResult();

        return $this->render('default/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/product/add", name="product_add")
     */
    public function addAction(Request $request, EntityManagerInterface $em)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        return $this->modify($product, $request, $em);
    }

    /**
     * @Route("/product/{productId}/delete", name="product_delete", requirements={"productId": "\d+"})
     */
    public function deleteAction($productId, Request $request, EntityManagerInterface $em)
    {
        $product = $em->getRepository('AppBundle:Product')
            ->find($productId);

        if (!$product) {
            $this->addFlash(
                'error',
                'No product found for id '.$productId
            );
            return $this->redirectToRoute('admin');
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash(
            'notice',
            'Product deleted!'
        );

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/product/{productId}/edit", name="product_edit", requirements={"productId": "\d+"})
     */
    public function editAction($productId, Request $request, EntityManagerInterface $em)
    {
        $product = $em->getRepository('AppBundle:Product')
            ->find($productId);

        if (!$product) {
            $this->addFlash(
                'error',
                'No product found for id '.$productId
            );
            return $this->redirectToRoute('admin');
        }

        return $this->modify($product, $request, $em);
    }

    private function modify(Product $product, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $em->persist($product);
            $em->flush();

            $admin_url = $this->container->get('router')->generate('admin');

            $this->addFlash(
                'notice',
                'Your changes were saved! <a href="'.$admin_url.'">Go back to admin page</a>'
            );

            // return $this->redirectToRoute('admin');
        }

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
