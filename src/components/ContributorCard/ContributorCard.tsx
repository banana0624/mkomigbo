// project-root/src/components/ContributorCard/ContributorCard.tsx

import React from 'react';
import styles from './ContributorCard.module.css';
import theme from '../../styles/theme.module.css';

type ContributorCardProps = {
  name: string;
  badge: 'newcomer' | 'momentum' | 'veteran';
};

const ContributorCard: React.FC<ContributorCardProps> = ({ name, badge }) => {
  return (
    <div className={styles.badgeWrapper} aria-label={`${badge.charAt(0).toUpperCase() + badge.slice(1)} Badge`}>
        {/* Static badge for immediate recognition */}
        <span className={styles.badge}>
            {badge}
        </span>

        {/* Animated badge for contributor celebration */}
        <span className={`${theme.badgeBounce} ${theme.transition} ${theme.textSuccess}`}>
            {badge}
        </span>
    </div>
  );
};

export default ContributorCard;
