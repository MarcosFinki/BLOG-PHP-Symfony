<?php

namespace App\Controller;

use App\Entity\Restaurante;
use App\Repository\RestauranteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RestauranteController extends AbstractController
{
    #[Route('/blog',name: "index", methods:["GET"])]
    public function index(): Response{
        return $this->render("restaurante/index.html.twig");
    }
    
    #[Route('/restaurante', name: 'mostrar_todos_restaurantes', methods:["GET"])]
    public function restaurante_index(RestauranteRepository $repo): Response
    {
        $lista = $repo->findAll();
        //$(nombre) = $(nombre)Repository->findOneBy( ['nombre' => 'Paella'] );
        //$(nombre) = $(nombre)Repository->find($id);
        //$(nombre) = $(nombre)Repository->findBy( ['nombre' => 'Paella'] );

        //return $this->json($lista);
        return $this->render("restaurante/restaurantes.html.twig", [
            "restaurantes"=> $lista
          ]);
    }

    #[Route('/restaurante/crear', name: 'crear_restaurante', methods: ["GET","POST"])]
    public function crearRestauranteForm (EntityManagerInterface $emi, Request $request ): Response{
	$restaurante = new Restaurante();
	
    $fb=$this->createFormBuilder($restaurante);
        $fb->add("nombre", TextType::class, [
            
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255]),
                new NotBlank()
            ]
        ]);
        $fb->add("direccion", TextType::class, [
            
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255]),
                new NotBlank()
            ]
        ]);
        $fb->add("telefono", TextType::class, [
            "required" => false,
            "constraints"=>[
                new Length(["min"=>1,"max"=> 12])
            ]
        ]);
        $fb->add("tipoDeCocina", TextType::class, [
            "required" => false,
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255])
            ]
        ]);
        $fb->add("Guardar", SubmitType::class);
    $formulario = $fb->getForm();
    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()) {
        $rest = $formulario->getData();
        $emi->persist($rest);
        $emi->flush();
        return $this->redirectToRoute("mostrar_todos_restaurantes");
    }else{
        return $this->render("restaurante/restaurante_crear.html.twig", ["formulario"=>$formulario]);
    };
}

    #[Route('/restaurante/actualizar/{idRestaurante}', name: 'actualizar_restaurante', methods: ["GET","POST"])]
    public function actuRestaurante (EntityManagerInterface $emi, Restauranterepository $repo, int $idRestaurante, Request $request): Response{

	$restaurante = $repo->find($idRestaurante);
	$fb=$this->createFormBuilder($restaurante);
        $fb->add("nombre", TextType::class, [
            
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255]),
                new NotBlank()
            ]
        ]);
        $fb->add("direccion", TextType::class, [
            
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255]),
                new NotBlank()
            ]
        ]);
        $fb->add("telefono", TextType::class, [
            "required" => false,
            "constraints"=>[
                new Length(["min"=>1,"max"=> 12])
            ]
        ]);
        $fb->add("tipoDeCocina", TextType::class, [
            "required" => false,
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255])
            ]
        ]);
        $fb->add("Guardar", SubmitType::class);

    $formulario = $fb->getForm();
    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()) {
        $restaurante = $formulario->getData();
        
        $emi->flush();
        return $this->redirectToRoute("mostrar_todos_restaurantes");
    }else{
        return $this->render("restaurante/restaurante_actualizar.html.twig", ["formulario"=>$formulario]);
    };
	}

    #[Route('/restaurante/{idRestaurante}', name: 'mostrar_uno_restaurante', methods:["GET"])]
    public function mostrarUnRest(RestauranteRepository $repo, int $idRestaurante): Response
    {
        $rest = $repo->find($idRestaurante);
        if ($rest == null) {
            return $this->json("Restaurante no encontrado", Response::HTTP_NOT_FOUND);
        }
        return $this->render("restaurante/restaurante.html.twig", [
            "restaurante"=> $rest
        ]);
    }

     #[ Route("/restaurante/eliminar/{idRestaurante}", name: 'eliminar_restaurante', methods: ["GET","POST"])]
    public function eliminarRestaurante (RestauranteRepository $repo, EntityManagerInterface $emi, int $idRestaurante): Response{
        $restaurante = $repo->find($idRestaurante);
            if(empty($restaurante->getVisitas()[0])){
                $emi->remove($restaurante);
                $emi->flush();
                $this->addFlash('success', 'Restaurante eliminado con éxito.');
            }else{
                $this->addFlash('error', 'No se puede eliminar un restaurante con visitas activas.');
            }
            return $this->redirectToRoute("mostrar_todos_restaurantes");
	}
}
