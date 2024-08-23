<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ProjectRepository $projectRepository,
    ) {
    }

    #[Route('', name: 'data', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $projects = $this->projectRepository->findBy([
            'user' => $this->getUser(),
        ]);

        $numberOfProjects = count($projects);
        $numberOfMessages = $this->countNumberOfMessages($projects);
        $numberOfLines = $this->countNumberOfLines($projects);
        $timeSaved = $this->getTimeSaved($numberOfLines);

        return new JsonResponse([
            'documents' => $numberOfProjects,
            'questions' => $numberOfMessages,
            'lines' => $numberOfLines,
            'timesaved' => $timeSaved,
        ]);
    }

    protected function countNumberOfMessages(array $projects): int
    {
        $numberOfMessages = 0;
        foreach ($projects as $project) {
            $numberOfMessages += count(array_filter($project->getMessages()->toArray(), fn($message) => $message->getOwner() === 'user'));
        }

        return $numberOfMessages;
    }

    protected function countNumberOfLines(array $projects): int
    {
        $numberOfLines = 0;
        foreach ($projects as $project) {
            $numberOfLines += $this->countRealLines($project->getContent());
        }

        return $numberOfLines;
    }

    protected function getTimeSaved(int $numberOfLines): int
    {
        // estimate how much it would take to the user to read each line in hours
        return $numberOfLines * 3;
    }

    protected function countRealLines(string $htmlContent): int
    {
        // Load HTML content
        $dom = new \DOMDocument();
        @$dom->loadHTML($htmlContent);

        // Extract text content
        $text = $this->extractText($dom);

        // Clean and split text by lines
        $lines = explode("\n", $text);
        $realLines = array_filter($lines, fn($line) => trim($line) !== '');

        return count($realLines);
    }

    protected function extractText(\DOMNode $node): string
    {
        $text = '';

        // Traverse child nodes
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $text .= $child->wholeText . "\n";;
            } elseif ($child->nodeType === XML_ELEMENT_NODE && !in_array($child->nodeName, ['script', 'style'])) {
                $text .= $this->extractText($child);
            }
        }

        return $text;
    }
}
