<div class="grid grid-cols-2 gap-4">
    <x-forms.input-field name="first_name" label="First Name" :value="auth()->user()->first_name" required />
    <x-forms.input-field name="second_name" label="Second Name" :value="auth()->user()->second_name" required />
</div>