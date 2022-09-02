<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\WebhookController;
use Psr\Log\LoggerInterface;

class <?php echo $class_name; ?> extends AbstractController
{
<?php echo $generator->generateRouteForControllerMethod($route_path, $route_name); ?>
    public function index(Request $request, WebhookController $webhook, LoggerInterface $logger): Response
    {
        try {
            $webhook = $webhook->checksum($request, '<?php echo $secret; ?>')->toArray();
            
            return new Response(200);
        } catch (\Exception $e) {
            $logger->error('Webhook Error: ' . $e->getMessage());
            
            return new Response(500);
        }
    }
}
