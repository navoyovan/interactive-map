@extends('layouts.app')

@section('content')
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                </div>
                <div class="flex items-center space-x-4">
                    {{-- <div class="text-sm text-gray-500">
                        <p>{{ $moderatedPosts->count() + $unmoderatedPosts->count() }} Posts • 
                           {{ $userComments->count() }} Comments</p>
                    </div> --}}
                    <a href="{{ route('dashboard') }}" class="text-sm px-3 py-1 bg-gray-100 border rounded hover:bg-gray-200">
                        🔄 Refresh
                    </a>
                </div>
            </div>
        </div>
    </header>
<div class="p-6 bg-gray-100 min-h-screen">


    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Profile Card --}}
        <div class="col-span-1 bg-white rounded-2xl p-4 shadow">
            <div class="flex items-center space-x-4 space-y-6">
                <img src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : 'https://media.discordapp.net/attachments/946064559930757201/1180797256794902538/20231203_030941.gif?ex=6842afa4&is=68415e24&hm=57e1a8f3e3c18cfec3dd14a7290ca6e64bbd2d5625971ecef6534830045646bb&' }}" alt="Profile Picture" class="w-20 h-20 rounded-full">
                <div>
                    <h2 class="text-xl font-semibold">{{ Auth::user()->name }}</h2>
                    <p class="text-gray-500 capitalize">{{ Auth::user()->role ?? 'User' }}</p>
                </div>
            </div>

            {{-- change profile image --}}

                    <div class="col-span-1 bg-white rounded-2xl p-4">
                <form method="POST" action="{{ route('profile.image') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="block mb-1 font-semibold">Change Profile Image</label>
                    <input type="file" name="image" accept="image/*" class="mb-2">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Upload</button>
                </form>

            </div>
        </div>

        {{-- Profile Update Form --}}
        <div class="col-span-1 bg-white rounded-2xl p-4 shadow space-y-6">
            {{-- <h3 class="text-xl font-semibold text-gray-800">Update Profile</h3> --}}

            {{-- Update Profile Information --}}
                    <div class="col-span-1 bg-white rounded-2xl p-4 ">
            <div>
                @include('profile.partials.update-profile-information-form')
            </div>
                    </div>
            {{-- delete account. --}}
            {{-- 
            <div>
                @include('profile.partials.delete-user-form')
            </div>
            --}}
        </div>

        {{-- Profile Update Form --}}
        <div class="col-span-1 bg-white rounded-2xl p-4 shadow space-y-6">
            {{-- <h3 class="text-xl font-semibold text-gray-800">Update Password</h3> --}}

                    {{-- Update Password --}}
                            <div class="col-span-1 bg-white rounded-2xl p-4 ">
            <div>
                @include('profile.partials.update-password-form')
            </div>
            </div>

            {{-- Two Factor Authentication --}}
        </div>

    </div>
</div>
@endsection
