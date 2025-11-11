@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Deals & Promotions
            </h2>
            <p class="text-dragon-silver-dark text-sm mt-1">Manage promotional offers and deals</p>
        </div>
        <a href="{{ route('admin.promotions.create') }}" 
           class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>Create Promotion
        </a>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dragon-black border-b border-dragon-border">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Title</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Min Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Period</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Claims</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($promotions as $promo)
                    <tr class="hover:bg-dragon-surface transition-colors">
                        <td class="px-6 py-4 text-dragon-silver">{{ $promo->id }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-dragon-silver">{{ $promo->title }}</span>
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-{{ $promo->status_color }}-600 text-{{ $promo->status_color }}-100">
                                        {{ $promo->status_label }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver">${{ number_format($promo->min_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs rounded {{ $promo->bonus_type == 'recurrent' ? 'bg-purple-600 text-purple-100' : 'bg-yellow-600 text-yellow-100' }}">
                                {{ ucfirst($promo->bonus_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.promotions.toggle-active', $promo) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="px-3 py-1 text-xs rounded transition-colors {{ $promo->is_active ? 'bg-green-600 hover:bg-green-700 text-green-100' : 'bg-gray-600 hover:bg-gray-700 text-gray-100' }}">
                                    {{ $promo->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver-dark text-sm">
                            {{ $promo->start_at->format('M d, Y H:i') }}<br>
                            to {{ $promo->end_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-dragon-silver">
                            {{ $promo->claimed_global }}
                            @if($promo->global_claim_limit)
                                / {{ $promo->global_claim_limit }}
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.promotions.show', $promo) }}" 
                                   class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.promotions.edit', $promo) }}" 
                                   class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.promotions.destroy', $promo) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-dragon-silver-dark">
                                No promotions found. <a href="{{ route('admin.promotions.create') }}" class="text-dragon-red hover:text-dragon-red-bright transition-colors">Create your first promotion</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
