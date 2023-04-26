<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" class="form-horizontal" action="{{ route('epi.contacts.store') }}">
                @csrf
                @method('POST')
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Datos del Contacto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Contacto/Pariente:</h5>
                    <div class="form-row">

                    <input type="hidden" name="patient_id" value="{{$original_id}}">

                    <input type="hidden" name="contact_id" value="{{$patient->id}}">

                    
                    

                        <fieldset class="form-group col-md-3">
                            <label for="for_run">Run u Otra identificación:</label>
                            <input type="text" class="form-control" value="{{ $patient->identification->value }}" style="text-transform: uppercase;" readonly>
                        </fieldset>

                        <fieldset class="form-group col-md-3">
                            <label for="for_name">Nombre:</label>
                            <input type="text" class="form-control" value="{{ $patient->given ?? '' }}" style="text-transform: uppercase;" readonly>
                        </fieldset>

                        <fieldset class="form-group col-md-3">
                            <label for="for_fathers_family">Apellido Paterno:</label>
                            <input type="text" class="form-control" value="{{ $patient->fathers_family ?? '' }}" style="text-transform: uppercase;" readonly>
                        </fieldset>

                        <fieldset class="form-group col-md-3">
                            <label for="for_mothers_family">Apellido Materno:</label>
                            <input type="text" class="form-control" value="{{ $patient->mothers_family ?? '' }}" style="text-transform: uppercase;" readonly>
                        </fieldset>
                    </div>

                    <hr>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>Ingreso los datos del contacto:</h5>

                            <div class="form-row">
                                <fieldset class="form-group col-md-3">
                                    <label for="for_last_contact_at">Fecha último contacto *</label>
                                    <input type="datetime-local" class="form-control" name="last_contact_at" id="for_last_contact_at" value="" required>
                                </fieldset>

                                <fieldset class="form-group col-md-3">
                                    <label for="for_register_at">Parentesco</label>
                                    <select class="form-control" name="relationship" id="for_relationship" title="Seleccione..." data-live-search="true" data-size="5" required>

                                        @if($patient->actualSex == 'Femenino')
                                        <option value=""></option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="grandmother">Abuela</option>
                                        <option value="sister in law">Cuñada</option>
                                        <option value="wife">Esposa</option>
                                        <option value="sister">Hermana</option>
                                        <option value="daughter">Hija</option>
                                        <option value="mother">Madre</option>
                                        <option value="cousin">Primo/a</option>
                                        <option value="niece">Sobrina</option>
                                        <option value="mother in law">Suegra</option>
                                        <option value="aunt">Tía</option>
                                        <option value="grandchild">Nieta</option>
                                        <option value="daughter in law">Nuera</option>
                                        <option value="girlfriend">Pareja</option>
                                        <option value="other">Otro</option>
                                        @elseif($patient->actualSex == 'Masculino')

                                        <option value=""></option>
                                        <option value="grandfather">Abuelo</option>
                                        <option value="brother in law">Cuñado</option>
                                        <option value="husband">Esposo</option>
                                        <option value="brother">Hermano</option>
                                        <option value="son">Hijo</option>
                                        <option value="grandchild">Nieto</option>
                                        <option value="father">Padre</option>
                                        <option value="boyfriend">Pareja</option>
                                        <option value="cousin">Primo/a</option>
                                        <option value="nephew">Sobrino</option>
                                        <option value="father in law">Suegro</option>
                                        <option value="uncle">Tío</option>
                                        <option value="son in law">Yerno</option>
                                        <option value="other">Otro</option>
                                        @else
                                        <option value="else">else</option>
                                        <option value=""></option>
                                        <option value="grandmother">Abuela</option>
                                        <option value="grandfather">Abuelo</option>
                                        <option value="sister in law">Cuñada</option>
                                        <option value="brother in law">Cuñado</option>
                                        <option value="wife">Esposa</option>
                                        <option value="husband">Esposo</option>
                                        <option value="sister">Hermana</option>
                                        <option value="brother">Hermano</option>
                                        <option value="daughter">Hija</option>
                                        <option value="son">Hijo</option>
                                        <option value="mother">Madre</option>
                                        <option value="father">Padre</option>
                                        <option value="cousin">Primo/a</option>
                                        <option value="niece">Sobrina</option>
                                        <option value="nephew">Sobrino</option>
                                        <option value="mother in law">Suegra</option>
                                        <option value="father in law">Suegro</option>
                                        <option value="aunt">Tía</option>
                                        <option value="uncle">Tío</option>
                                        <option value="grandchild">Nieta/o</option>
                                        <option value="daughter in law">Nuera</option>
                                        <option value="son in law">Yerno</option>
                                        <option value="girlfriend">Pareja (Femenino)</option>
                                        <option value="boyfriend">Pareja (Masculino)</option>
                                        <option value="other">Otro</option>
                                        @endif
                                    </select>
                                </fieldset>

                                <fieldset class="form-group col-md-3">
                                    <label for="for_live_together">¿Viven Juntos?</label>
                                    <select class="form-control selectpicker" name="live_together" id="for_live_together" title="Seleccione..." data-size="2">
                                        <option value="1">Si</option>
                                        <option value="0">No</option>
                                    </select>
                                </fieldset>

                            </div>

                            <div class="form-row">
                                <fieldset class="form-group col-md-12">
                                    <label for="for_epivigila">Observación:</label>
                                    <input type="text" class="form-control" name="observation" autocomplete="off">
                                </fieldset>
                            </div>
                            <hr>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary float-right">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>