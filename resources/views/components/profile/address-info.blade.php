<div class="space-y-4">
    <x-forms.textarea name="address" label="Address" :value="auth()->user()->address" required />
    
    <div class="grid grid-cols-2 gap-4">
        <x-forms.input-field name="city" label="City" :value="auth()->user()->city" required />
        <x-forms.select name="state" label="State" :options="$stateOptions" :selected="auth()->user()->state" required />
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <x-forms.input-field name="zip_code" label="Zip Code" :value="auth()->user()->zip_code" required />
        <x-forms.input-field name="country" label="Country" :value="auth()->user()->country" required />
    </div>
</div>