<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ProductController extends AbstractController
{
    #[Route('/product/new', name:'form_product')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $em = $doctrine->getManager();

        //Introducir categoria a mano (crear objeto y añadirlo al form)
        $category = $doctrine->getRepository(Category::class)->find(1);

        if($form->isSubmitted() && $form->isValid()){
            $product = $form->getData();
            $product->setCategory($category);
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('product_show');
        }

        return $this->renderForm('product/new.html.twig', [
            'titulo' => 'Alta producto',
            'form' => $form,
        ]);
    }


    #[Route('/product/create', name:'create_product')]
    public function createProduct(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName('Monitor');
        $product->setPrice(25000);
        $product->setDescription('Ergonomic and stylish!');

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }


    #[Route('/product/{id}', name:'product_showId')]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        //$product = $doctrine->getRepository(Product::class)->find($id);
        $product = $productRepository->find($id);

        return new Response('Check out this great product: '.$product->getName());

        // or render a template
        // in the template, print things with {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }

    #[Route('/product', name:'product_show')]
    public function showAll(ProductRepository $productRepository): Response
    {
        //$product = $doctrine->getRepository(Product::class)->find($id);
        $product = $productRepository->findAll();

        if (!$product) {
            throw $this->createNotFoundException(
                'Producto no encontrado'
            );
        }

        return $this->render('product/index.html.twig', ['product' => $product]);

        // or render a template
        // in the template, print things with {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }


    #[Route('/product/edit/{id}')]
    public function update(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $product->setName('New product name!');
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }

    #[Route('/product/delete/{id}')]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new Response('Producto borrado correctamente');
    }

    #[Route('/product/price/{minPrice}')]
    public function price(ManagerRegistry $doctrine, int $minPrice): Response
    {

        $products = $doctrine->getRepository(Product::class)->findAllGreaterThanPrice($minPrice);

        if (!$products) {
            throw $this->createNotFoundException(
                'No product found'
            );
        }

        for($i=0; $i<count($products); $i++){
            return new Response('Product objects greater than a '.$minPrice.' price: '.$products[$i]->getName());
        }
    }




}
