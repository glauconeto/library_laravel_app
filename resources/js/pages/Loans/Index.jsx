import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function LoansIndex({ loans, filters, isLibrarian }) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleStatusChange = (e) => {
        setSelectedStatus(e.target.value);
    };

    const applyFilter = () => {
        const params = new URLSearchParams();
        
        if (selectedStatus) {
            params.append('status', selectedStatus);
        }

        router.get(route('loans.index', params.toString()));
    };

    const resetFilter = () => {
        setSelectedStatus('');
        router.get(route('loans.index'));
    };

    const handleReturn = (loanId) => {
        if (confirm('Tem certeza que deseja marcar este livro como devolvido?')) {
            router.post(route('loans.return', loanId));
        }
    };

    const handleExtend = (loanId) => {
        if (confirm('Deseja estender este empréstimo por 7 dias?')) {
            router.post(route('loans.extend', loanId));
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            case 'overdue':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            case 'returned':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
        }
    };

    const getDaysText = (loan) => {
        const today = new Date();
        const dueDate = new Date(loan.due_date);
        const diffTime = dueDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (loan.returned_at) {
            return `Devolvido em ${new Date(loan.returned_at).toLocaleDateString()}`;
        }

        if (diffDays < 0) {
            return `${Math.abs(diffDays)} dias de atraso`;
        } else if (diffDays === 0) {
            return 'Devolve hoje';
        } else {
            return `${diffDays} dias restantes`;
        }
    };

    return (
        <AppLayout>
            <Head title="Meus Empréstimos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                            {isLibrarian ? 'Todos os Empréstimos' : 'Meus Empréstimos'}
                        </h1>

                        {/* Filters */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
                            <button
                                onClick={() => setShowFilters(!showFilters)}
                                className="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Filtros
                                </span>
                                <svg
                                    className={`w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform ${
                                        showFilters ? 'rotate-180' : ''
                                    }`}
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            {showFilters && (
                                <div className="px-6 pb-6 border-t border-gray-200 dark:border-gray-700">
                                    <div className="flex flex-col sm:flex-row gap-4 items-end pt-4">
                                <div className="flex-1 max-w-xs">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Filtro de Status
                                    </label>
                                    <select
                                        value={selectedStatus}
                                        onChange={handleStatusChange}
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    >
                                        <option value="">Todos os Empréstimos</option>
                                        <option value="active">Ativo</option>
                                        <option value="overdue">Atrasado</option>
                                        <option value="returned">Devolvido</option>
                                    </select>
                                </div>

                                <div className="flex gap-2">
                                    <PrimaryButton onClick={applyFilter}>
                                        Aplicar Filtro
                                    </PrimaryButton>
                                    <button
                                        onClick={resetFilter}
                                        className="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition duration-150"
                                    >
                                        Limpar
                                    </button>
                                </div>
                            </div>
                                </div>
                            )}
                        </div>

                        {/* Stats */}
                        <div className="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {loans.total} empréstimos encontrados
                        </div>
                    </div>

                    {/* Loans List */}
                    {loans.data.length > 0 ? (
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead className="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Livro
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                {isLibrarian ? 'Usuário' : 'Retirado'}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Data de Devolução
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status
                                            </th>
                                            {!isLibrarian && (
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Ações
                                                </th>
                                            )}
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        {loans.data.map((loan) => (
                                            <tr key={loan.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div>
                                                            <Link
                                                                href={route('books.show', loan.book.id)}
                                                                className="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300"
                                                            >
                                                                {loan.book.title}
                                                            </Link>
                                                            <div className="text-sm text-gray-500 dark:text-gray-400">
                                                                por {loan.book.author}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900 dark:text-white">
                                                        {isLibrarian ? loan.user.name : new Date(loan.loan_date).toLocaleDateString()}
                                                    </div>
                                                    {!isLibrarian && (
                                                        <div className="text-sm text-gray-500 dark:text-gray-400">
                                                            Retirado em {new Date(loan.loan_date).toLocaleDateString()}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900 dark:text-white">
                                                        {new Date(loan.due_date).toLocaleDateString()}
                                                    </div>
                                                    <div className="text-sm text-gray-500 dark:text-gray-400">
                                                        {getDaysText(loan)}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(loan.status)}`}>
                                                        {loan.status}
                                                    </span>
                                                </td>
                                                {!isLibrarian && (
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div className="flex gap-2">
                                                            {loan.status === 'active' && !loan.is_overdue && (
                                                                <PrimaryButton
                                                                    onClick={() => handleExtend(loan.id)}
                                                                    className="px-3 py-1 text-xs"
                                                                >
                                                                    Estender
                                                                </PrimaryButton>
                                                            )}
                                                            {loan.status === 'active' && (
                                                                <button
                                                                    onClick={() => handleReturn(loan.id)}
                                                                    className="px-3 py-1 bg-green-600 text-white text-xs rounded-md hover:bg-green-700 transition duration-150"
                                                                >
                                                                    Devolver
                                                                </button>
                                                            )}
                                                            {loan.status === 'returned' && (
                                                                <Link
                                                                    href={route('books.show', loan.book.id)}
                                                                    className="px-3 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 transition duration-150"
                                                                >
                                                                    Emprestar Novamente
                                                                </Link>
                                                            )}
                                                        </div>
                                                    </td>
                                                )}
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-8">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    Nenhum empréstimo encontrado
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-6">
                                    {isLibrarian 
                                        ? 'Nenhum empréstimo corresponde ao seu filtro atual.'
                                        : 'Você não pegou nenhum livro emprestado ainda. Comece explorando nossa coleção!'
                                    }
                                </p>
                                {!isLibrarian && (
                                    <Link href={route('books.index')}>
                                        <PrimaryButton>
                                            Ver Livros
                                        </PrimaryButton>
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Pagination */}
                    {loans.links && loans.links.length > 3 && (
                        <div className="mt-8 flex justify-center">
                            <div className="flex gap-2">
                                {loans.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`px-3 py-2 rounded-md text-sm ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
