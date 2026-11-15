import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthCard from '@/Components/AuthCard';
import Input from '@/Components/Input';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import Checkbox from '@/Components/Checkbox';

interface LoginFormData {
    email: string;
    password: string;
    remember: boolean;
}

interface LoginProps {
    canResetPassword: boolean;
    status: string | null;
}

// Mensagens de erro intuitivas em português
const ERROR_MESSAGES = {
    email_required: 'Informe seu e-mail para continuar.',
    email_invalid: 'Digite um e-mail válido (exemplo: nome@empresa.com).',
    password_required: 'Informe sua senha para continuar.',
    credentials_invalid: 'E-mail ou senha incorretos. Verifique seus dados e tente novamente.',
};

export default function Login({ canResetPassword, status }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginFormData>({
        email: '',
        password: '',
        remember: false,
    });

    const [clientErrors, setClientErrors] = useState<Partial<Record<keyof LoginFormData, string>>>({});

    const validateEmail = (email: string): string | null => {
        if (!email.trim()) return ERROR_MESSAGES.email_required;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) return ERROR_MESSAGES.email_invalid;
        return null;
    };

    const validatePassword = (password: string): string | null => {
        if (!password) return ERROR_MESSAGES.password_required;
        return null;
    };

    const handleEmailChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setData('email', value);
        // Limpa erro do email quando o usuário começa a digitar
        if (clientErrors.email) {
            const error = validateEmail(value);
            setClientErrors(prev => ({ ...prev, email: error || undefined }));
        }
    };

    const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setData('password', value);
        // Limpa erro da senha quando o usuário começa a digitar
        if (clientErrors.password) {
            const error = validatePassword(value);
            setClientErrors(prev => ({ ...prev, password: error || undefined }));
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Validação client-side
        const emailError = validateEmail(data.email);
        const passwordError = validatePassword(data.password);

        if (emailError || passwordError) {
            setClientErrors({
                email: emailError || undefined,
                password: passwordError || undefined,
            });
            return;
        }

        // Limpa erros client-side
        setClientErrors({});

        post(route('login'), {
            onFinish: () => reset('password'),
            onError: (errors) => {
                // Se o backend retornou erro de credenciais, mostra mensagem amigável
                if (errors.email || errors.password) {
                    setClientErrors({
                        email: ERROR_MESSAGES.credentials_invalid,
                    });
                }
            },
        });
    };

    // Determina qual mensagem exibir para cada campo
    const getEmailError = (): string | undefined => {
        return clientErrors.email || errors.email;
    };

    const getPasswordError = (): string | undefined => {
        return clientErrors.password || errors.password;
    };

    // Verifica se há erro de credenciais do backend
    const hasCredentialsError = errors.email?.includes('credenciais') || 
                                errors.email?.includes('incorretos') ||
                                errors.password;

    return (
        <AuthCard>
            <Head title="Login" />

            <div className="text-center mb-8">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                    Sistema de Livraria
                </h1>
            </div>

            {status && (
                <div className="mb-4 text-sm text-green-600 dark:text-green-400 text-center bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                    {status}
                </div>
            )}

            {/* Alerta de erro geral (credenciais inválidas) */}
            {hasCredentialsError && !clientErrors.email && !clientErrors.password && (
                <div className="mb-4 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 flex items-start gap-2">
                    <svg className="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                    </svg>
                    <span>{ERROR_MESSAGES.credentials_invalid}</span>
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-5" noValidate>
                <div>
                    <InputLabel htmlFor="email" value="E-mail" className="mb-1.5" />
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        placeholder="Digite seu email"
                        className={`block w-full px-4 py-3 ${getEmailError() ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}`}
                        autoComplete="username"
                        isFocused={true}
                        onChange={handleEmailChange}
                        onBlur={() => {
                            const error = validateEmail(data.email);
                            if (error) setClientErrors(prev => ({ ...prev, email: error }));
                        }}
                    />
                    <InputError className="mt-2" message={getEmailError()} />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Senha" className="mb-1.5" />
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        placeholder="Digite sua senha"
                        className={`block w-full px-4 py-3 ${getPasswordError() ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}`}
                        autoComplete="current-password"
                        onChange={handlePasswordChange}
                        onBlur={() => {
                            const error = validatePassword(data.password);
                            if (error) setClientErrors(prev => ({ ...prev, password: error }));
                        }}
                    />
                    <InputError className="mt-2" message={getPasswordError()} />
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <span className="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            Lembrar-me
                        </span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline"
                        >
                            Esqueceu sua senha?
                        </Link>
                    )}
                </div>

                <PrimaryButton
                    className="w-full py-3 text-base font-medium"
                    disabled={processing}
                >
                    {processing ? 'Entrando...' : 'Entrar'}
                </PrimaryButton>
            </form>
        </AuthCard>
    );
}
