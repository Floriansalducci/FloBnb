<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AdType;
use App\Form\ImageType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repository)
    {

        $ads = $repository->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
     * Permet de créer une annonce
     * Mettre avant la methode show sinon symfo cherche un slug new a la place la route
     *
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     */

    public function create(Request $request, EntityManagerInterface $manager){
        $ad = new Ad();

        $form= $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            foreach ($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }

            $ad->setAuthor($this->getUser());

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée"
            );


            return $this->redirectToRoute('ads_show', [
                'slug'=> $ad->getSlug()
            ]);
        }


        return $this->render('ad/new.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher une annonce
     *
     * @Route("/ads/{slug}", name="ads_show")
     *
     */
    public function show(Ad $ad){

        return $this->render('ad/show.html.twig', [
            'ad'=> $ad
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")
     *
     */
    public function edit(Request $request, Ad $ad,EntityManagerInterface $manager ){

        $form= $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            foreach ($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée"
            );


            return $this->redirectToRoute('ads_show', [
                'slug'=> $ad->getSlug()
            ]);
        }

        return $this->render('ad/edit.html.twig', [
            'form'=> $form->createView(),
            'ad'=> $ad
        ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la supprimer")
     *
     */
    public function delete(Ad $ad,EntityManagerInterface $manager){
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimer"
        );

        return $this->redirectToRoute("ads_index");
    }

}
