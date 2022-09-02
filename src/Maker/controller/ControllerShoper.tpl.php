<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;

class <?php echo $class_name; ?> extends AbstractController
{
<?php echo $generator->generateRouteForControllerMethod($route_path, $route_name); ?>
    public function index(ApiController $api): Response
    {
<?php if ($with_template) { ?>
        return $this->render('<?php echo $template_name; ?>', [
            'controller_name' => '<?php echo $class_name; ?>',
        ]);
<?php } else { ?>
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => '<?php echo $relative_path; ?>',
        ]);
<?php } ?>
    }
}
