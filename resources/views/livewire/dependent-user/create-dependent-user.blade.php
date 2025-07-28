<div>
    <x-slot name="heading">
        Datos del Usuario Dependiente
    </x-slot>
    <form wire:submit="save">
        <x-filament::fieldset class="col-[--col-span-default] grid grid-cols-2">
            <x-slot name="label">
                Datos Personales
            </x-slot>

            <x-filament::input.wrapper class="m-2" prefix="Nombre">
                <x-filament::input type="text" wire:model="data.nombre" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper prefix="Apellido Paterno">
                <x-filament::input type="text" wire:model="data.apellido_paterno" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper prefix="Apellido Materno">
                <x-filament::input type="text" wire:model="data.apellido_materno" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper prefix="RUN">
                <x-filament::input type="text" wire:model="data.run" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper prefix="DV">
                <x-filament::input type="text" wire:model="data.dv" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper prefix="Fecha de Nacimiento">
                <x-filament::input type="date" wire:model="data.fecha_nacimiento" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Sexo</x-filament::input>
                <x-filament::input.select wire:model="data.sexo" required>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Género</x-filament::input>
                <x-filament::input.select wire:model="data.genero" required>
                    <option value="">Seleccionar</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    </x-filament::select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Nacionalidad</x-filament::input>
                <x-filament::input.select wire:model="data.nacionalidad" required>
                    <option value="">Seleccionar</option>
                    </x-filament::select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Comuna</x-filament::input>
                <x-filament::input type="text" wire:model="data.comuna" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Calle</x-filament::input>
                <x-filament::input type="text" wire:model="data.calle" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Número</x-filament::input>
                <x-filament::input type="text" wire:model="data.numero" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Departamento</x-filament::input>
                <x-filament::input type="text" wire:model="data.departamento" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Teléfono</x-filament::input>
                <x-filament::input type="text" wire:model="data.telefono" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Previsión</x-filament::input>
                <x-filament::input.select wire:model="data.prevision" required>
                    <option value="">Seleccionar</option>
                    </x-filament::select>
            </x-filament::input.wrapper>
        </x-filament::fieldset>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Diagnóstico</x-filament::input>
            <x-filament::input type="text" wire:model="data.diagnostico" required />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Fecha de Ingreso</x-filament::input>
            <x-filament::input type="date" wire:model="data.fecha_ingreso" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Fecha de Egreso</x-filament::input>
            <x-filament::input type="date" wire:model="data.fecha_egreso" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Establecimiento</x-filament::input>
            <x-filament::input.select wire:model="data.establecimiento">
                <option value="">Seleccionar</option>
                </x-filament::select>
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Visitas Integrales</x-filament::input>
            <x-filament::input type="text" wire:model="data.visitas_integrales" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Visitas Tratamiento</x-filament::input>
            <x-filament::input type="text" wire:model="data.visitas_tratamiento" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">EMP EMPAM</x-filament::input>
            <x-filament::input type="text" wire:model="data.emp_empam" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">ELEAM</x-filament::input>
            <x-filament::input type="text" wire:model="data.eleam" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">UPP</x-filament::input>
            <x-filament::input type="text" wire:model="data.upp" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Plan Elaborado</x-filament::input>
            <x-filament::input type="text" wire:model="data.plan_elaborado" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Plan Evaluado</x-filament::input>
            <x-filament::input type="text" wire:model="data.plan_evaluado" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Neumo</x-filament::input>
            <x-filament::input type="text" wire:model="data.neumo" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Influenza</x-filament::input>
            <x-filament::input type="text" wire:model="data.influenza" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">COVID-19</x-filament::input>
            <x-filament::input type="text" wire:model="data.covid_19" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Información Extra</x-filament::input>
            <x-filament::input type="text" area wire:model="data.extra_info" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Ayuda Técnica</x-filament::input>
            <x-filament::input type="text" wire:model="data.ayuda_tecnica" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Fecha Ayuda Técnica</x-filament::input>
            <x-filament::input type="date" wire:model="data.ayuda_tecnica_fecha" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Entrega Alimentación</x-filament::input>
            <x-filament::input type="text" wire:model="data.entrega_alimentacion" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Fecha Entrega Alimentación</x-filament::input>
            <x-filament::input type="date" wire:model="data.entrega_alimentacion_fecha" />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Sonda SNG</x-filament::input>
            <x-filament::input.select wire:model="data.sonda_sng">
                <option value="">No aplica</option>
                <option value="si">Sí</option>
                <option value="no">No</option>
                </x-filament::select>
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Sonda Urinaria</x-filament::input>
            <x-filament::input.select wire:model="data.sonda_urinaria">
                <option value="">No aplica</option>
                </x-filament::select>
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Previsión Cuidador</x-filament::input>
            <x-filament::input.select wire:model="data.prevision_cuidador">
                <option value="">Seleccionar</option>
                <option value="Isapre">Isapre</option>
                <option value="Fonasa">Fonasa</option>
                </x-filament::select>
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input type="label">Talla Pañal</x-filament::input>
            <x-filament::input type="text" wire:model="data.talla_panal" />
        </x-filament::input.wrapper>
        <!-- </div> -->

        {{-- Checkbox para indicar si tiene cuidador --}}
        <x-filament::input.wrapper>
            <x-filament::input.checkbox wire:model="hasCaregiver">¿Tiene Cuidador?</x-filament::input.checkbox>
        </x-filament::input.wrapper>

        {{-- Datos del Cuidador (Ocultos inicialmente) --}}
        @if ($hasCaregiver)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::input.wrapper>
                <x-filament::input type="label">Nombre Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.nombre_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Apellido Paterno Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.apellido_paterno_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Apellido Materno Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.apellido_materno_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Fecha de Nacimiento Cuidador</x-filament::input>
                <x-filament::input type="date" wire:model="data.fecha_nacimiento_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Run Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.run_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">DV Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.dv_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Sexo Cuidador</x-filament::input>
                <x-filament::input.select wire:model="data.sexo_cuidador" required>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Género Cuidador</x-filament::input>
                <x-filament::input.select wire:model="data.genero_cuidador" required>
                    <option value="">Seleccionar</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    </x-filament::select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Nacionalidad Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.nacionalidad_cuidador" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Parentesco Cuidador</x-filament::input>
                <x-filament::input.select wire:model="data.parentesco_cuidador">
                    <option value="">Seleccionar</option>
                    <option value="esposo">Conjuge</option>
                    <option value="hijo">Hijo/a</option>
                    <option value="madrastro">Cuidador Pagado</option>
                    <option value="otro">Otro</option>
                    </x-filament::select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">EMPAM Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.empam_cuidador" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Zarit Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.zarit_cuidador" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Inmunizaciones Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.inmunizaciones_cuidador" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Plan Elaborado Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.plan_elaborado_cuidador" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Plan Evaluado Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.plan_evaluado_cuidador" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Capacitación Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.capacitacion_cuidador" />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input type="label">Estipendio Cuidador</x-filament::input>
                <x-filament::input type="text" wire:model="data.estipendio_cuidador" />
            </x-filament::input.wrapper>
        </div>
        @endif

        <x-filament::button type="submit">
            Guardar
        </x-filament::button>
    </form>

</div>