@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-4 p-4 sm:mb-8 sm:p-8">
            <div class="flex flex-col items-start justify-between gap-4">
                <div class="w-full">
                    <h2 class="heading-1 text-2xl font-bold sm:text-3xl">System Configuration</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage system-wide settings and configurations</p>
                </div>
            </div>
        </div>

        <!-- Configuration Form Section -->
        <div class="animate-slide-in delay-200">
            <form class="space-y-8" id="configForm">
                @foreach ($configs as $group => $groupConfigs)
                    <div class="card rounded-lg bg-white p-6">
                        <h2 class="mb-4 text-xl font-semibold">{{ ucfirst($group) }} Settings</h2>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            @foreach ($groupConfigs as $config)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="{{ $config->key }}">
                                        {{ $config->description }}
                                    </label>
                                    <div class="mt-1">
                                        @switch($config->type)
                                            @case('boolean')
                                                <select
                                                    class="form-select block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    id="{{ $config->key }}" name="{{ $config->key }}">
                                                    <option value="true" {{ $config->value === 'true' ? 'selected' : '' }}>Enabled
                                                    </option>
                                                    <option value="false" {{ $config->value === 'false' ? 'selected' : '' }}>
                                                        Disabled</option>
                                                </select>
                                            @break

                                            @case('textarea')
                                                <textarea
                                                    class="form-textarea block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    id="{{ $config->key }}" name="{{ $config->key }}" rows="3">{{ $config->value }}</textarea>
                                            @break

                                            @default
                                                <input
                                                    class="form-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    id="{{ $config->key }}" name="{{ $config->key }}"
                                                    type="{{ $config->type === 'number' ? 'number' : 'text' }}"
                                                    value="{{ $config->value }}"
                                                    {{ $config->type === 'number' ? 'step="0.01"' : '' }}>
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-indigo-700 hover:to-purple-700"
                        type="submit">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
