<?php
namespace VOCS\PlatformBundle\Controller\API;


use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use VOCS\PlatformBundle\Entity\Demands;
use VOCS\PlatformBundle\Entity\Lists;
use VOCS\PlatformBundle\Entity\User;
use VOCS\PlatformBundle\Entity\Words;
use VOCS\PlatformBundle\Form\DemandsType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class DemandsController extends Controller
{

    /**
     * GET
     */

    /**
     * @ApiDoc(
     *     section="Demands",
     *     description="Récupère toutes les demands",
     *     output= { "class"=Demands::class, "collection"=true, "groups"={"demand"} }
     *     )
     *
     * @Rest\View(serializerGroups={"demand"})
     * @Rest\Get("/rest/demands")
     */
    public function getDemandsAction(Request $request)
    {
        $demands = $this->getDoctrine()->getRepository(Demands::class)->findAll();

        $view = View::create($demands);
        $view->setHeader('Access-Control-Allow-Origin', '*');

        return $view;
    }


    /**
     * @ApiDoc(
     *     section="Demands",
     *     description="Récupère une demand",
     *     output= { "class"=Demands::class, "collection"=false, "groups"={"demand"} }
     *     )
     *
     * @Rest\View(serializerGroups={"demand"})
     * @Rest\Get("/rest/demands/{id}")
     */
    public function getDemandAction(Request $request)
    {
        $demand = $this->getDoctrine()->getRepository(Demands::class)->find($request->get('id'));

        $view = View::create($demand);
        $view->setHeader('Access-Control-Allow-Origin', '*');

        return $view;
    }

    /**
     * @ApiDoc(
     *     section="Demands",
     *     description="Récupère les demands d'un user",
     *     output= { "class"=Demands::class, "collection"=false }
     *     )
     *
     * @Rest\View(serializerGroups={"demand"})
     * @Rest\Get("/rest/demands/users/{user_id}")
     */
    public function getDemandsUserAction(Request $request)
    {
        $demandSend = $this->getDoctrine()->getRepository(Demands::class)->findByUserSend($request->get('user_id'));
        $demandReceive = $this->getDoctrine()->getRepository(Demands::class)->findByUserReceive($request->get('user_id'));

        $demands = ["demandSend" => $demandSend, "demandReceive" => $demandReceive];

        return $demands;
    }






    /**
     * POST
     */

    /**
     * @ApiDoc(
     *     section="Demands",
     *    description="Crée une demand",
     *    input={"class"=DemandsType::class, "name"="", "groups"={"demand"}}
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"demand"})
     * @Rest\Post("/rest/demands")
     *
     */
    public function postDemandAction(Request $request)
    {
        $demand = new Demands();

        $form = $this->createForm(DemandsType::class, $demand);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if($demand->getWordTrad() != null) {

                $repWord = $em->getRepository(Words::class);
                $word = $repWord->findOneBy(array('content' => $demand->getWordTrad()->getWord()->getContent(), 'language' => $demand->getWordTrad()->getWord()->getLanguage()));
                if ($word != null) {
                    $demand->getWordTrad()->setWord($word);
                }
                $trad = $repWord->findOneBy(array('content' => $demand->getWordTrad()->getTrad()->getContent(), 'language' => $demand->getWordTrad()->getTrad()->getLanguage()));
                if ($trad != null) {
                    $demand->getWordTrad()->setTrad($trad);
                }
            }
            $em->persist($demand);
            $em->flush();


            return $demand;
        } else {

            return $form;
        }

    }

    /**
     * PUT
     */

    /**
     * @ApiDoc(
     *     section="Demands",
     *    description="Change une demand",
     *    input={"class"=DemandsType::class, "name"=""}
     * )
     *
     * @Rest\View(serializerGroups={"demand"})
     * @Rest\Put("/rest/demands/{id}")
     */
    public function putDemandsAction(Request $request)
    {
        return $this->updateDemand($request, true);
    }

    /**
     * @ApiDoc(
     *    section="Demands",
     *    description="Patch une demand",
     *    input={"class"=DemandsType::class, "name"=""}
     * )
     *
     * @Rest\View(serializerGroups={"demand"})
     * @Rest\Patch("/rest/demands/{id}")
     */
    public function patchDemandsAction(Request $request)
    {
        return $this->updateDemand($request, false);
    }


    private function updateDemand(Request $request, $clearMissing)
    {
        $demand = $this->getDoctrine()->getRepository(Demands::class)->find($request->get('id'));

        if (empty($demand)) {
            return new JsonResponse(['message' => 'Demand not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(DemandsType::class, $demand);

        $form->submit($request->request->all(), $clearMissing);


        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            return $demand;
        } else {
            return $form;
        }
    }

    /**
     *DELETE
     */

    /**
     * @ApiDoc(
     *     section="Demands",
     *     description="Remove une demand",
     *     output= { "class"=Demands::class, "collection"=false, "groups"={"demand"} }
     *     )
     *
     * @Rest\View(serializerGroups={"demand"})
     * @Rest\Delete("/rest/demands/{id}")
     */
    public function deleteUserClasse(Request $request)
    {

        $demand = $this->getDoctrine()->getRepository(Demands::class)->find($request->get('id'));

        $em = $this->getDoctrine()->getManager();

        $em->remove($demand);
        $em->flush();

        return $demand;
    }
}