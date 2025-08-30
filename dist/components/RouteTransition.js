import { jsx as _jsx } from "react/jsx-runtime";
import { CSSTransition, TransitionGroup } from 'react-transition-group';
import { useTransitionConfig } from '../hooks/useTransitionConfig';
import './RouteTransition.css';
const RouteTransition = ({ children, key, role }) => {
    const { type, duration, direction, easing } = useTransitionConfig(key, role);
    const seoClass = `transition-${key.replace('/', '-')}`;
    return (_jsx(TransitionGroup, { children: _jsx(CSSTransition, { timeout: duration, classNames: type, children: _jsx("div", { className: `route-transition ${direction} ${seoClass}`, style: { transitionTimingFunction: easing }, children: children }) }, key) }));
};
export default RouteTransition;
