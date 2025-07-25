<?php



namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


class LoginController extends Controller

{



    use AuthenticatesUsers;



    protected $redirectTo = '/dashboard';

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function __construct()

    {

        $this->middleware('guest')->except('logout');

    }

    public function showLoginForm()
    {
        // return DB::table('general_settings')->latest()->first();

        if(isset($_COOKIE['language']))
            App::setLocale($_COOKIE['language']);
        else
            App::setLocale('en');
        //getting theme
        if(isset($_COOKIE['theme']))
            $theme = $_COOKIE['theme'];
        else
            $theme = 'light';
        //get general setting value
        $general_setting =  Cache::remember('general_setting', 60*60*24*365, function () {
            return DB::table('general_settings')->latest()->first();
        });

        if(!$general_setting) {
            DB::unprepared(file_get_contents(public_path('tenant_necessary.sql')));
            $general_setting =  Cache::remember('general_setting', 60*60*24*365, function () {
                return DB::table('general_settings')->latest()->first();
            });
            copy(public_path("landlord/images/logo/").optional($general_setting)->site_logo, "logo/".optional($general_setting)->site_logo);
        }
        $numberOfUserAccount = User::where('is_active', true)->count();
        return view('backend.auth.login', compact('theme', 'general_setting', 'numberOfUserAccount'));
    }

    public function login(Request $request)
    {

        $input = $request->all();

        $this->validate($request, [
            'name' => 'required',
            'password' => 'required',
        ]);

        $fieldType = filter_var($request->name, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if(auth()->attempt(array($fieldType => $input['name'], 'password' => $input['password'])))
        {
            setcookie('login_now', 1, time() + (86400 * 1), "/");
            return redirect('/dashboard');
        }
        else {
            return redirect()->route('login')->with('error','Username And Password Are Wrong.');
        }
    }
}
