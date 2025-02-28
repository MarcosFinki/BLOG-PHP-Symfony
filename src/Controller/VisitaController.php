<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Visita;
use App\Repository\RestauranteRepository;
use App\Repository\VisitaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Length;

class VisitaController extends AbstractController
{
    #[Route('/blog',name: "index", methods:["GET"])]
    public function index(): Response{
        return $this->render("restaurante/index.html.twig");
    }
    
    #[Route('/visita', name: 'mostrar_todos_visitas', methods:["GET"])]
    public function restaurante_index(VisitaRepository $repo): Response
    {
        $lista = $repo->findAll();
        //$(nombre) = $(nombre)Repository->findOneBy( ['nombre' => 'Paella'] );
        //$(nombre) = $(nombre)Repository->find($id);
        //$(nombre) = $(nombre)Repository->findBy( ['nombre' => 'Paella'] );

        //return $this->json($lista);
        return $this->render("visita/visitas.html.twig", [
            "visitas"=> $lista
          ]);
    }

    #[Route('/visita/crear', name: 'crear_visita', methods: ["GET","POST"])]
    public function crearVisitaForm (EntityManagerInterface $emi, RestauranteRepository $rerepo, Request $request ): Response{
	$visita = new Visita();
	
    $fb=$this->createFormBuilder($visita);
    $fb->add("restaurante", TextType::class, [
        "mapped" => false,
        "constraints"=>[
            new Length(["min"=>1,"max"=> 255])
        ]
    ]);
        $fb->add("valoracion", IntegerType::class, [
            
            "constraints"=>[
                new Range(["min"=>1,"max"=> 10]),
                new NotBlank()
            ]
        ]);
        $fb->add("comentario", TextType::class, [
            "required" => false,
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255])
            ]
        ]);
        $fb->add("Guardar", SubmitType::class);
    $formulario = $fb->getForm();
    $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()){
            $restauranteNombre = $formulario->get("restaurante")->getData();
            $restaurante = $rerepo->findOneBy(["Nombre" => $restauranteNombre]);
                if($restaurante!=null){
                    $visita->setRestaurante($restaurante);
                    $emi->persist($visita);
                    $emi->flush();
                    return  $this->redirectToRoute("mostrar_todos_visitas");
                }else {
                    $this->addFlash("error", "Recuerda poner un Restaurante existente");
                    return $this->render("visita/visita_crear.html.twig", ["formulario" => $formulario]);
                }
        }else{
            return $this->render("visita/visita_crear.html.twig", ["formulario" => $formulario]);
        }
    }
    #[Route('/visita/actualizar/{idVisita}', name: 'actualizar_visita', methods: ["GET","POST"])]
    public function actuVisita (EntityManagerInterface $emi, Visitarepository $repo, int $idVisita, Request $request): Response{
    
    $visita = $repo->find($idVisita);
	$nombreRes = $visita -> getRestaurante() -> getNombre();

    $fb=$this->createFormBuilder($visita);
    
    $fb->add("valoracion", IntegerType::class, [
            
        "constraints"=>[
            new Range(["min"=>1,"max"=> 10]),
            new NotBlank()
        ]
    ]);
        $fb->add("comentario", TextType::class, [
            "required" => false,
            "constraints"=>[
                new Length(["min"=>1,"max"=> 255])
            ]
        ]);
        $fb->add("Guardar", SubmitType::class);
    $formulario = $fb->getForm();
    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()){
                $emi->persist($visita);
                $emi->flush();
                return  $this->redirectToRoute("mostrar_todos_visitas");            
        }else{
            return $this->render("visita/visita_actualizar.html.twig", ["formulario" => $formulario, "nombreRes" => $nombreRes]);
        }
    }

    #[Route('/visita/{idVisita}', name: 'mostrar_uno_visita', methods:["GET"])]
    public function mostrarUnVisita(VisitaRepository $repo, int $idVisita): Response
    {
        $visita = $repo->find($idVisita);
        if ($visita == null) {
            return $this->json("Visita no encontrada", Response::HTTP_NOT_FOUND);
        }
        return $this->render("visita/visita.html.twig", [
            "visita"=> $visita
        ]);
    }

     #[ Route("/visita/eliminar/{idVisita}", name: 'eliminar_visita', methods: ["GET","POST"])]
    public function eliminarVisita (VisitaRepository $repo, EntityManagerInterface $emi, int $idVisita): Response{
        $visita = $repo->find($idVisita);
        $emi->remove($visita);
        $emi->flush();
        $this->addFlash('success', 'Visita eliminada con éxito.');
        return $this->redirectToRoute("mostrar_todos_visitas");
	}
}
