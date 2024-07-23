import Dashboard from './components/Dashboard.vue';
import ListReports from './pages/reports/ListReports.vue';
import ReportForm from './pages/reports/ReportForm.vue';
import UserList from './pages/users/UserList.vue';
import UpdateSetting from './pages/settings/UpdateSetting.vue';
import UpdateProfile from './pages/profile/UpdateProfile.vue';
import Login from './pages/auth/Login.vue';
import OrganizationList from './pages/orgs/OrgList.vue';
import OrgReports from './pages/org_reports/OrgReports.vue';
import ListPosts from './pages/posts/ListPosts.vue';
import PostForm from './pages/posts/PostForm.vue';


export default[
    {
        path: '/login',
        name: 'admin.login',
        component: Login,
    },
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: Dashboard,
    },
    {
        path: '/admin/reports',
        name: 'admin.reports',
        component: ListReports,
    },
    {
        path: '/admin/org-reports',
        name: 'admin.orgreports',
        component: OrgReports,
    },
    {
        path: '/admin/reports/create',
        name: 'admin.reports.create',
        component: ReportForm,
    },
    {
        path: '/admin/reports/:id/edit',
        name: 'admin.reports.edit',
        component: ReportForm,
    },
    {
        path: '/admin/organizations',
        name: 'admin.organizations',
        component: OrganizationList,
    },
    {
        path: '/admin/users',
        name: 'admin.users',
        component: UserList,
    },
    {
        path: '/admin/settings',
        name: 'admin.settings',
        component: UpdateSetting,
    },
    
    {
        path: '/admin/profile',
        name: 'admin.profile',
        component: UpdateProfile,
    },
    {
        path: '/admin/posts/:category',
        name: 'admin.posts',
        component: ListPosts,
    },
    {
        path: '/admin/posts/create',
        name: 'admin.posts.create',
        component: PostForm,
    },
    {
        path: '/admin/posts/:id/edit',
        name: 'admin.posts.edit',
        component: PostForm,
    },
   
]