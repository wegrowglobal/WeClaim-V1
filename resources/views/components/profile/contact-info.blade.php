<div class="grid grid-cols-2 gap-4">
    <x-forms.input-field name="email" label="Email" type="email" :value="auth()->user()->email" required />
    <x-forms.input-field name="phone" label="Phone" type="tel" :value="auth()->user()->phone" required />
</div>