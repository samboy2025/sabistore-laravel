<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class Controller
 *
 * This abstract class serves as the base controller for the application. It extends Laravel's
 * `BaseController` and includes the `AuthorizesRequests` and `ValidatesRequests` traits,
 * providing common functionality for request authorization and validation to all other
 * controllers that extend it.
 *
 * @package App\Http\Controllers
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
