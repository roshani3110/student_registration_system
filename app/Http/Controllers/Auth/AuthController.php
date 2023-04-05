<?php
  
namespace App\Http\Controllers\Auth;
  
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('auth.login');
    }  
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registration()
    {
        return view('auth.registration');
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postLogin(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user->is_admin || $user->verified_by_admin) {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
       
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                return redirect()->to('/users')
                            ->withSuccess('You have Successfully loggedin');
            }

            return redirect("login")->withSuccess('Oppes! You have entered invalid credentials');
        } else {
            return redirect("login")->withError('Admin will verify your details soon, after that you are able to login');
        }
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);
        $img_name = 'img_'.time().'.'.$request->photo->getClientOriginalExtension();
        $path = $request->file('photo')->storeAs('public/photo',$img_name);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->name),
            'dob' => $request->dob,
            'photo' => $path,
            'address' => $request->address
        ]);

        Mail::send('emails.register', array (
        ), function($message) use ($user) {
            $message->to('admin@admin.com');
            $message->from('r@gmail.com');
            $message->subject('New Student Register');
        });

        return redirect("dashboard")->withSuccess('Great! You have register Successfully');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function dashboard()
    {
        if(Auth::check()){
            return view('dashboard');
        }
  
        return redirect("login")->withSuccess('Opps! You do not have access');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function logout() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }
}
