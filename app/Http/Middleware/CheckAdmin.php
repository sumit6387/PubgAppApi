<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = Admin::where('email',$request->email)->get()->first();
        if(!$admin){
            return response()->json([
                'status' => false,
                'msg'  => 'You Are Not A Admin.'
            ]);
        }
        return $next($request);
    }
}
