<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use PanKrok\ShoperAppstoreBundle\Controller\WebhookController;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class <?php echo $class_name; ?> extends AbstractController
{
<?php echo $generator->generateRouteForControllerMethod($route_path, $route_name); ?>
    public function index(Request $request, WebhookController $webhook, LoggerInterface $logger): Response
    {
        try {
            $webhook->checksum($request, '<?php echo $secret; ?>');
            $api = $webhook->getApiClient();

            // Your webhook logic here.
            // Example: $products = $api->product->get()->getBodyArray();

            return new Response('', Response::HTTP_OK);
        } catch (ShoperApiException $e) {
            $logger->error('Webhook API error: ' . $e->getMessage(), ['error' => $e->getShoperError()]);

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            $logger->error('Webhook error: ' . $e->getMessage());

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
