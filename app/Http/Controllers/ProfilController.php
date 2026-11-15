<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function admin() { return view('profil.edit', ['user'=>auth()->user()]); }
    public function siswa() { return view('profil.edit', ['user'=>auth()->user()]); }
    public function update(Request $request) {
        $user=auth()->user();
        $data=$request->validate([
            'name'=>'required|string|max:100',
            'email'=>['required','email','max:150',Rule::unique('users')->ignore($user->id)],
            'password'=>['nullable','confirmed',Password::min(8)],
        ]);
        $user->name=$data['name'];
        $user->email=$data['email'];
        if(!empty($data['password'])) $user->password=Hash::make($data['password']);
        $user->save();
        return back()->with('success','Profil berhasil diperbarui.');
    }
}
