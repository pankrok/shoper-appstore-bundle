<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use PanKrok\ShoperAppstoreBundle\Controller\AppstoreBillingController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class <?php echo $class_name; ?> extends AbstractController
{
<?php echo $generator->generateRouteForControllerMethod($route_path, $route_name); ?>
    public function index(Request $request, AppstoreBillingController $billing): Response
    {
        return $billing->init($request->request->all());
    }
}
