<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class <?php echo $class_name; ?> extends AbstractController
{
<?php echo $generator->generateRouteForControllerMethod($route_path, $route_name); ?>
    public function index(ApiController $api): Response
    {
        try {
<?php if ($with_template) { ?>
            return $this->render('<?php echo $template_name; ?>', [
                'controller_name' => '<?php echo $class_name; ?>',
            ]);
<?php } else { ?>
            return $this->json([
                'message' => 'Welcome to your new controller!',
                'path' => '<?php echo $controller_path; ?>',
            ]);
<?php } ?>
        } catch (ShoperApiException $e) {
            throw $e;
        }
    }
}
