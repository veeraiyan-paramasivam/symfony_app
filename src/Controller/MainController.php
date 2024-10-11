<?php

namespace App\Controller;
use App\Form\PostType;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class MainController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        $posts = $this->em->getRepository(Post::class)->findAll();

        return $this->render('main/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/create-post', name: 'create-post')]
    public function createPost(Request $request, SluggerInterface $slugger) 
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $oldImage = $post->getImageName();
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // directory where the image will be stored
                        $newFilename
                    );
                    $post->setImageName($newFilename);
                    if ($oldImage && file_exists($this->getParameter('images_directory') . '/' . $oldImage)) {
                        unlink($this->getParameter('images_directory') . '/' . $oldImage);
                    }
                } catch (FileException $e) {
                    // Handle exceptions during the file upload process
                }
            }
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('message', 'Insert Successfully');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('main/post.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/edit-post/{id}', name: 'edit-post')]
    public function editPost(Request $request, $id, SluggerInterface $slugger)
    {
        $post = $this->em->getRepository(Post::class)->find($id);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $oldImage = $post->getImageName();
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // directory where the image will be stored
                        $newFilename
                    );
                    $post->setImageName($newFilename);
                    if ($oldImage && file_exists($this->getParameter('images_directory') . '/' . $oldImage)) {
                        unlink($this->getParameter('images_directory') . '/' . $oldImage);
                    }
                } catch (FileException $e) {
                    // Handle exceptions during the file upload process
                }
               
            }
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('message','updated Successfully');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('main/post.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }
    #[Route('/delete-post/{id}', name: 'delete-post')]
    public function deletePost(Request $request, $id)
    {
        $post = $this->em->getRepository(post::class)->find($id);
        $this->em->remove($post);
        $this->em->flush();
        $this->addFlash('message', 'Deleted Successfully');
        return $this->redirectToRoute('app_main');
    }
}
