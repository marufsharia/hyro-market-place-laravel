import { Link } from '@inertiajs/react';

export default function PluginCard({ plugin }) {
    return (
        <Link href={route('market.show', plugin.slug)} className="group block h-full">
            <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 h-full hover:shadow-xl hover:border-teal-500 transition-all duration-300 flex flex-col">
                <div className="flex items-start justify-between mb-4">
                    <img 
                        src={plugin.logo_path ? `/storage/${plugin.logo_path}` : 'https://via.placeholder.com/150'} 
                        alt={plugin.name} 
                        className="w-14 h-14 rounded-lg object-cover bg-slate-100 dark:bg-slate-700" 
                    />
                    <span className="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                        Active
                    </span>
                </div>
                
                <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2 group-hover:text-teal-600 transition-colors">{plugin.name}</h3>
                <p className="text-slate-500 dark:text-slate-400 text-sm mb-4 line-clamp-2 flex-1">{plugin.description}</p>
                
                <div className="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700 mt-auto">
                    <div className="flex items-center gap-1 text-amber-500">
                        <span>★</span> <span className="text-sm font-bold text-slate-900 dark:text-white">{plugin.rating_avg}</span>
                    </div>
                    <div className="text-xs text-slate-500 dark:text-slate-400">↓ {plugin.downloads}</div>
                </div>
            </div>
        </Link>
    );
}