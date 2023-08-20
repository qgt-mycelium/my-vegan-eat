<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Category;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    /** ------------------- Constructor ------------------- */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChartBuilderInterface $chartBuilder
    ) {
    }

    /** ------------------- Routes ------------------- */
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        /** TODO:
         * Create a dashboard page with a menu to access the different pages, different pages are: comments, users, posts, settings.
         * On the dashboard page, list the number of comments, users, posts (use the count() function).
         * Chart the number of comments, users, posts per month for the last 12 months (use the chart.js library).
         * List of top 5 posts with the most comments. List of top 5 users with the most comments. List of top 5 posts with the most likes.
         * */

        // Number of comments, users, posts
        $statistics = $this->getNumberOfCommentsPostsAndUsers();

        if (false === $statistics) {
            throw $this->createNotFoundException('No statistics found');
        }

        foreach ($statistics as $key => $value) {
            $chart = match ($key) {
                'comments' => $this->getChartForComments(),
                'posts' => $this->getChartForPosts(),
                default => null,
            };

            $statistics[$key] = ['sum' => $value, 'chart' => $chart];
        }

        // Return view
        return $this->render('pages/dashboard/index.html.twig', [
            'statistics' => $statistics,
        ]);
    }

    /** ------------------- Get numbers of comments, posts and users ------------------- */

    /**
     * @return array<string, mixed>|false
     */
    private function getNumberOfCommentsPostsAndUsers()
    {
        return $this->entityManager
            ->getConnection()
            ->prepare(
                (string) preg_replace(
                    "/\s+/",
                    ' ',
                    'SELECT 
                    (SELECT COUNT(comment.id) FROM comment WHERE comment.is_published = 1 AND comment.is_deleted = 0) as comments, 
                    (SELECT COUNT(post.id) FROM post WHERE post.published_at IS NOT NULL) as posts,
                    (SELECT COUNT(user.id) FROM user) as users;'
                )
            )
            ->executeQuery()
            ->fetchAssociative();
    }

    /** ------------------- Get charts ------------------- */

    /**
     * Get chart for comments.
     */
    private function getChartForComments(): Chart
    {
        $comments = $this->entityManager->getRepository(Comment::class)->findNumberOfCommentsPerMonthForLast12Months();

        $data = [
            'chart_type' => Chart::TYPE_BAR,
            'datasets' => [
                'New comments' => $comments['published'],
                'Deleted comments' => $comments['deleted'],
                'Waiting approval' => $comments['waiting_approval'],
            ],
        ];

        return $this->createChart($data['datasets'], $data['chart_type']);
    }

    /**
     * Get chart for posts.
     */
    private function getChartForPosts(): Chart
    {
        /** @var Category[] $categories */
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $this->entityManager->getRepository(Category::class)->hydratePosts($categories);

        $datasets = [];
        foreach ($categories as $index => $categorie) {
            $datasets[$categorie->getName()] = ['position' => $index, 'count' => $categorie->getPosts()->count()];
        }

        $data = [
            'chart_type' => Chart::TYPE_LINE,
            'datasets' => [
                'Number of posts' => $datasets,
            ],
        ];

        return $this->createChart($data['datasets'], $data['chart_type'], array_keys($datasets));
    }

    /**
     * Create a chart with the data.
     *
     * @param array<string, array<int|string, array<string, mixed>>> $datasets
     * @param array<string>                                          $labels
     */
    private function createChart($datasets, string $type = Chart::TYPE_BAR, array $labels = []): Chart
    {
        $sets = [];

        $background = [
            'rgba(255, 205, 86)',
            'rgba(255, 99, 132)',
            'rgba(75, 192, 192)',
            'rgba(255, 159, 64)',
            'rgba(54, 162, 235)',
            'rgba(153, 102, 255)',
            'rgba(201, 203, 207)',
            'rgba(255, 205, 86)',
            'rgba(255, 99, 132)',
            'rgba(75, 192, 192)',
            'rgba(255, 159, 64)',
            'rgba(54, 162, 235)',
        ];

        foreach ($datasets as $label => $value) {
            // @phpstan-ignore-next-line
            $data = array_combine(array_column($value, 'position'), array_column($value, 'count'));
            $data = $data + array_pad([], 12, 0);
            ksort($data);

            $sets[] = [
                'label' => $label,
                'backgroundColor' => count($sets) < count($background) ? $background[count($sets)] : sprintf('rgba(%d, %d, %d)', rand(0, 255), rand(0, 255), rand(0, 255)),
                'data' => array_values($data),
            ];
        }

        $chart = $this->chartBuilder->createChart($type);

        $chart->setData([
            'labels' => [] != $labels ? $labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
            'datasets' => $sets,
        ]);

        $chart->setOptions([
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'align' => 'start',
                ],
            ],
        ]);

        return $chart;
    }
}
