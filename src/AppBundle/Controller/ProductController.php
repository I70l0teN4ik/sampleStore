<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Form\ProductType;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Show Products list.
     *
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository(Product::class)->queryLatest();
        /** @var Paginator $paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            Product::NUM_ITEMS
        );

        return $this->render('product/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Create new Product entity.
     *
     * @Route("/admin/new-product", name="product_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'New Product created successfully.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit a Product entity.
     *
     * @Route("/admin/product/{id}", name="product_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param Product $product
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, Product $product)
    {
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'New Product created successfully.');

            return $this->redirectToRoute('homepage');
        }
        $deleteForm = $this->createDeleteForm($product);

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'show_confirmation' => true,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Show Product entity details.
     *
     * @Route("/products/{id}", requirements={"id": "\d+"}, name="product_show")
     * @Method("GET")
     *
     * @param Product $product
     * @return Response
     */
    public function showAction(Product $product)
    {
        if ('dev' === $this->getParameter('kernel.environment')) {
            dump($product, $this->get('security.token_storage')->getToken()->getUser(), new \DateTime());
        }

        $deleteForm = $this->createDeleteForm($product);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'show_confirmation' => true,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Delete Product entity.
     *
     * @Route("/products/{id}", name="product_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param Product $product
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($product);
            $em->flush();

            $this->addFlash('success', 'Product deleted successfully');
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * Creates a form to delete a Product entity by id.
     *
     * @param Product $product
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', ['id' => $product->getId()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
