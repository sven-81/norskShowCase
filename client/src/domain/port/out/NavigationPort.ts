export interface NavigationPort {
    navigateTo(path: string): Promise<void>
}

