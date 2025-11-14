<?php

    namespace App\Models;

    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    class User extends Authenticatable
    {
        use Notifiable;

        protected $connection = 'mysql_aicc';
        protected $table = 'tb_user';
        protected $primaryKey = 'id';
        public $incrementing = true;
        protected $keyType = 'int';

        public $timestamps = false;

        protected $fillable = [
            'nama','section_id','usr','pswd','email','no_hp','kode_user',
            'is_active','level','is_user_computer','created_at','image_sign',
        ];

        protected $hidden = ['pswd'];

 
        public function getAuthPassword()
        {
            return $this->pswd;
        }
    }
