<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordReset;

class UserPasswordReset extends Component
{
    public $user;
    public $message = '';

    public function resetPassword() 
    {
        
        if($this->user->officialEmail) {
            $newPassword = substr(str_shuffle(MD5(microtime())), 0, 6);
            $this->user->password = bcrypt($newPassword);
            $this->user->save();
            Mail::to($this->user->officialEmail)->send(new PasswordReset($this->user,$newPassword));
            $this->message = $newPassword;
        }
        else {
            $this->message = "No tiene registrado un correo oficial";
        }
    }

    public function render()
    {
        return view('livewire.user-password-reset');
    }
}
