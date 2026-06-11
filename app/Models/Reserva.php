<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model {
    protected $table = 'reserva';
    protected $primaryKey = 'id_reserva';
    public $timestamps = false;
    protected $fillable = ['data_reserva', 'hora', 'numero_pessoas', 'estado', 'id_cliente', 'id_empregado'];

    // Relacionamento com o Cliente
    public function cliente() {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    // Relacionamento Muitos-para-Muitos com Mesas (via reserva_mesa)
    public function mesas() {
        return $this->belongsToMany(Mesa::class, 'reserva_mesa', 'id_reserva', 'id_mesa');
    }
}