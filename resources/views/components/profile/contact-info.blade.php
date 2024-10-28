<div class="grid-to-flex-col">
    <x-forms.input-field name="email" label="Email" type="email" :value="auth()->user()->email" required />
    <x-forms.input-field name="phone" label="Phone" type="tel" :value="auth()->user()->phone" required />
</div>